<template>
  <div class="seller-list">
    <div class="header">
      <h2>Список продавцов</h2>
      <div class="actions">
        <input 
          v-model="search" 
          @input="searchSellers"
          type="text" 
          placeholder="Поиск по названию или ОГРН..."
          class="search-input"
        />
        <button @click="openCreateModal" class="btn btn-primary">
          Добавить продавца
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">
      ⏳ Загрузка...
    </div>

    <div v-else>
      <div class="filters">
        <select v-model="hasOgrn" @change="filterByOgrn" class="filter-select">
          <option value="">Все продавцы</option>
          <option value="true">С ОГРН</option>
          <option value="false">Без ОГРН</option>
        </select>
        
        <select v-model="sortBy" @change="sortSellers" class="filter-select">
          <option value="created_at">По дате создания</option>
          <option value="title">По названию</option>
        </select>
        
        <select v-model="sortOrder" @change="sortSellers" class="filter-select">
          <option value="desc">По убыванию</option>
          <option value="asc">По возрастанию</option>
        </select>
      </div>

      <div class="sellers-grid">
        <div v-for="seller in sellers" :key="seller.id" class="seller-card">
          <div class="seller-header">
            <h3 class="seller-title" :title="seller.title">{{ seller.short_title }}</h3>
            <div class="seller-actions">
              <button @click="viewProducts(seller)" class="btn btn-sm btn-info" :title="'Товары (' + seller.products_count + ')'">
                📦 {{ seller.products_count }}
              </button>
              <button @click="editSeller(seller)" class="btn btn-sm btn-warning" title="Редактировать">
                ✏️
              </button>
              <button @click="deleteSeller(seller)" class="btn btn-sm btn-danger" title="Удалить">
                🗑️
              </button>
            </div>
          </div>
          
          <div class="seller-info">
            <div class="info-item" v-if="seller.address">
              📍 <span>{{ seller.address }}</span>
            </div>
            
            <div class="info-item" v-if="seller.ogrn">
              🏢 <span>ОГРН: {{ seller.ogrn }}</span>
            </div>
            
            <div class="info-item" v-if="seller.products_count > 0">
              💰 <span>Общая стоимость: {{ formatCurrency(seller.total_value) }}</span>
            </div>
          </div>
          
          <div class="seller-footer">
            <span class="created-date">
              Создан: {{ formatDate(seller.created_at) }}
            </span>
            <div class="seller-badges">
              <span v-if="seller.has_ogrn" class="badge badge-success">С ОГРН</span>
              <span v-else class="badge badge-secondary">Без ОГРН</span>
            </div>
          </div>
        </div>
        
        <div v-if="sellers.length === 0" class="no-data">
          👥
          <p>Продавцы не найдены</p>
        </div>
      </div>

      <div class="pagination" v-if="totalPages > 1">
        <button 
          @click="goToPage(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="btn btn-sm"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <span class="page-info">
          Страница {{ currentPage }} из {{ totalPages }}
        </span>
        
        <button 
          @click="goToPage(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="btn btn-sm"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Modal для создания/редактирования продавца -->
    <SellerForm 
      v-if="showModal"
      :seller="selectedSeller"
      @close="closeModal"
      @saved="onSellerSaved"
    />

    <!-- Modal для просмотра продуктов продавца -->
    <SellerProducts
      v-if="showProductsModal"
      :seller="selectedSeller"
      @close="closeProductsModal"
    />
  </div>
</template>

<script>
import { SellerAPI } from '../services/api.js'
import SellerForm from './SellerForm.vue'
import SellerProducts from './SellerProducts.vue'

export default {
  name: 'SellerList',
  components: {
    SellerForm,
    SellerProducts
  },
  data() {
    return {
      sellers: [],
      loading: false,
      search: '',
      hasOgrn: '',
      sortBy: 'created_at',
      sortOrder: 'desc',
      currentPage: 1,
      totalPages: 1,
      showModal: false,
      showProductsModal: false,
      selectedSeller: null
    }
  },
  mounted() {
    this.loadSellers()
  },
  methods: {
    async loadSellers() {
      this.loading = true
      try {
        const params = {
          search: this.search,
          has_ogrn: this.hasOgrn,
          sort_by: this.sortBy,
          sort_order: this.sortOrder,
          page: this.currentPage
        }
        
        const response = await SellerAPI.getSellers(params)
        
        if (response.success) {
          this.sellers = Array.isArray(response.data) ? response.data : response.data.data
          this.totalPages = response.data.last_page || 1
        } else {
          console.error('Ошибка загрузки продавцов')
        }
      } catch (error) {
        console.error('Error loading sellers:', error)
        
        // Проверяем, если это ошибка авторизации
        if (error.response && error.response.status === 401) {
          // Очищаем токен и перенаправляем на страницу входа
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: null, authenticated: false }
          }))
          this.$router.push('/login')
          return
        }
        
        // Проверяем, если ответ не JSON (например, HTML редирект)
        if (error.response && error.response.headers['content-type'] && 
            !error.response.headers['content-type'].includes('application/json')) {
          // Это HTML редирект, пользователь не авторизован
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: null, authenticated: false }
          }))
          this.$router.push('/login')
          return
        }
        
        console.error('Ошибка загрузки продавцов')
      } finally {
        this.loading = false
      }
    },
    
    searchSellers() {
      this.currentPage = 1
      this.loadSellers()
    },
    
    filterByOgrn() {
      this.currentPage = 1
      this.loadSellers()
    },
    
    sortSellers() {
      this.currentPage = 1
      this.loadSellers()
    },
    
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
        this.loadSellers()
      }
    },
    
    openCreateModal() {
      this.selectedSeller = null
      this.showModal = true
    },
    
    editSeller(seller) {
      this.selectedSeller = seller
      this.showModal = true
    },
    
    viewProducts(seller) {
      this.selectedSeller = seller
      this.showProductsModal = true
    },
    
    async deleteSeller(seller) {
      try {
        const response = await SellerAPI.deleteSeller(seller.id)
        if (response.success) {
          console.log('Продавец успешно удален')
          this.loadSellers()
        } else {
          console.error(response.message || 'Ошибка удаления продавца')
        }
      } catch (error) {
        console.error('Error deleting seller:', error)
        console.error('Ошибка удаления продавца')
      }
    },
    
    closeModal() {
      this.showModal = false
      this.selectedSeller = null
    },
    
    closeProductsModal() {
      this.showProductsModal = false
      this.selectedSeller = null
    },
    
    onSellerSaved() {
      this.closeModal()
      this.loadSellers()
    },
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU')
    },
    
    formatCurrency(value) {
      if (!value) return '0 ₽'
      return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
      }).format(value)
    }
  }
}
</script>

<style scoped>
.seller-list {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header h2 {
  margin: 0;
  color: #333;
}

.actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 300px;
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.loading {
  font-size: 18px;
}

.sellers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.seller-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.seller-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.seller-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.seller-title {
  margin: 0;
  font-size: 18px;
  color: #333;
  flex: 1;
}

.seller-actions {
  display: flex;
  gap: 5px;
}

.seller-actions .btn {
  min-width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.seller-info {
  padding: 15px 20px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  color: #666;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item i {
  width: 16px;
  color: #999;
  font-size: 16px;
}

.seller-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.created-date {
  font-size: 12px;
  color: #999;
}

.seller-badges {
  display: flex;
  gap: 5px;
}

.badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
}

.badge-success {
  background: #d4edda;
  color: #155724;
}

.badge-secondary {
  background: #e2e3e5;
  color: #6c757d;
}

.no-data {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px;
  color: #666;
}

.no-data {
  font-size: 64px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 30px;
}

.page-info {
  color: #666;
  font-size: 14px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-info:hover {
  background: #138496;
}

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

@media (max-width: 768px) {
  .sellers-grid {
    grid-template-columns: 1fr;
  }
  
  .header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .actions {
    flex-direction: column;
  }
  
  .search-input {
    width: 100%;
  }
  
  .filters {
    flex-direction: column;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .seller-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .seller-actions {
    align-self: flex-end;
  }
}
</style>
