<template>
  <div class="product-list">
    <div class="header">
      <h2>Список товаров</h2>
      <div class="actions">
        <input 
          v-model="search" 
          @input="searchProducts"
          type="text" 
          placeholder="Поиск по названию или серийному номеру..."
          class="search-input"
        />
        <button @click="openCreateModal" class="btn btn-primary">
          Добавить товар
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">
      ⏳ Загрузка...
    </div>

    <div v-else>
      <div class="filters">
        <select v-model="selectedSeller" @change="filterBySeller" class="filter-select">
          <option value="">Все продавцы</option>
          <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
            {{ seller.title }}
          </option>
        </select>
        
        <select v-model="sortBy" @change="sortProducts" class="filter-select">
          <option value="created_at">По дате создания</option>
          <option value="title">По названию</option>
          <option value="price">По цене</option>
          <option value="date_of_buying">По дате покупки</option>
        </select>
        
        <select v-model="sortOrder" @change="sortProducts" class="filter-select">
          <option value="desc">По убыванию</option>
          <option value="asc">По возрастанию</option>
        </select>
      </div>

      <div class="products-grid">
        <div v-for="product in products" :key="product.id" class="product-card" @click="viewProductDetails(product)">
          <div class="product-header">
            <h3 class="product-title" :title="product.title">{{ product.title }}</h3>
            <div class="product-actions" @click.stop>
              <button @click.stop="viewProductDetails(product)" class="btn btn-sm btn-info" title="Просмотр">
                👁️
              </button>
              <button @click="editProduct(product)" class="btn btn-sm btn-warning" title="Редактировать">
                ✏️
              </button>
              <button @click="deleteProduct(product)" class="btn btn-sm btn-danger" title="Удалить">
                🗑️
              </button>
            </div>
          </div>
          
          <div class="product-info">
            <div class="info-item" v-if="product.model">
              📱 <span>{{ product.model }}</span>
            </div>
            
            <div class="info-item" v-if="product.serial_number">
              🔢 <span>Серийный номер: {{ product.serial_number }}</span>
            </div>
            
            <div class="info-item" v-if="product.price">
              💰 <span>Цена: {{ product.formatted_price }}</span>
            </div>
            
            <div class="info-item" v-if="product.warranty_period">
              🛡️ <span>Гарантия: {{ product.formatted_warranty_period }}</span>
            </div>
            
            <div class="info-item" v-if="product.seller">
              🏪 <span>Продавец: {{ product.seller.title }}</span>
            </div>
          </div>
          
                 <div class="product-footer">
                   <span class="purchase-date">
                     Куплен: {{ formatDate(product.date_of_buying) }}
                   </span>
                   <div class="product-badges">
                     <span v-if="product.serial_number" class="badge badge-success">С серийным номером</span>
                     <span v-else class="badge badge-secondary">Без серийного номера</span>
                     <span v-if="product.claims_count > 0" class="badge badge-warning" @click.stop="showProductClaims(product)">
                       ⚠️ Претензий: {{ product.claims_count }}
                     </span>
                   </div>
                 </div>
        </div>
        
        <div v-if="products.length === 0" class="no-data">
          📦
          <p>Товары не найдены</p>
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

    <!-- Modal для создания/редактирования товара -->
    <ProductForm 
      v-if="showModal"
      :product="selectedProduct"
      :sellers="sellers"
      :consumers="consumers"
      @close="closeModal"
      @saved="onProductSaved"
      @seller-created="onSellerCreated"
      @consumer-created="onConsumerCreated"
    />

           <!-- Форма просмотра товара -->
           <ProductViewForm 
             v-if="showViewForm"
             :product="selectedProduct"
             @close="closeViewForm"
             @edit="editProduct"
             @delete="deleteProduct"
             @edit-seller="editSellerFromProduct"
             @seller-updated="onSellerUpdated"
           />

           <!-- Всплывающее окно со списком претензий товара -->
           <div v-if="showClaimsModal" class="claims-popup-overlay" @click="closeClaimsModal">
             <div class="claims-popup" @click.stop>
               <div class="claims-popup-header">
                 <h4>Претензии по товару: {{ selectedProductForClaims?.title }}</h4>
                 <button @click="closeClaimsModal" class="close-btn">
                   <i class="fas fa-times"></i>
                 </button>
               </div>
               <div class="claims-popup-body">
                 <div v-if="productClaimsLoading" class="loading">
                   ⏳ Загрузка претензий...
                 </div>
                 <div v-else-if="productClaims.length === 0" class="no-claims">
                   📋 Претензий по данному товару нет
                 </div>
                 <div v-else class="claims-list">
                   <div v-for="claim in productClaims" :key="claim.id" class="claim-item">
                 <div class="claim-header">
                   <h5 class="claim-title">{{ getTypeLabel(claim.type) }}</h5>
                   <span class="claim-status" :class="'status-' + claim.status">
                     {{ getStatusLabel(claim.status) }}
                   </span>
                 </div>
                     <div class="claim-info">
                       <div class="info-row" v-if="claim.claimed_amount">
                         <span class="label">Сумма:</span>
                         <span class="value">{{ formatCurrency(claim.claimed_amount) }}</span>
                       </div>
                       <div class="info-row">
                         <span class="label">Дата подачи:</span>
                         <span class="value">{{ formatDate(claim.claim_date) }}</span>
                       </div>
                     </div>
                     <div class="claim-actions">
                       <button @click="editClaimFromProduct(claim)" class="btn btn-sm btn-warning" title="Редактировать">
                         ✏️
                       </button>
                       <button @click="deleteClaimFromProduct(claim)" class="btn btn-sm btn-danger" title="Удалить">
                         🗑️
                       </button>
                     </div>
                   </div>
                 </div>
               </div>
               <div class="claims-popup-footer">
                 <button @click="closeClaimsModal" class="btn btn-secondary btn-sm">
                   Закрыть
                 </button>
               </div>
             </div>
           </div>
         </div>
</template>

<script>
import { ProductAPI, SellerAPI, AuthAPI, ClaimAPI, ConsumerAPI } from '../services/api.js'
import ProductForm from './ProductForm.vue'
import ProductViewForm from './ProductViewForm.vue'

export default {
  name: 'ProductList',
  components: {
    ProductForm,
    ProductViewForm
  },
         data() {
           return {
             products: [],
             sellers: [],
             consumers: [],
             loading: false,
             search: '',
             selectedSeller: '',
             sortBy: 'created_at',
             sortOrder: 'desc',
             currentPage: 1,
             totalPages: 1,
             showModal: false,
             showViewForm: false,
             selectedProduct: null,
             showClaimsModal: false,
             selectedProductForClaims: null,
             productClaims: [],
             productClaimsLoading: false
           }
         },
  mounted() {
    this.loadProducts()
    this.loadSellers()
    this.loadConsumers()
  },
  methods: {
    async loadProducts() {
      this.loading = true
      
      try {
        const params = {
          search: this.search,
          seller_id: this.selectedSeller,
          sort_by: this.sortBy,
          sort_order: this.sortOrder,
          page: this.currentPage
        }
        
        const response = await ProductAPI.getProducts(params)
        
        if (response.success) {
          this.products = Array.isArray(response.data) ? response.data : response.data.data
          this.totalPages = response.data.last_page || 1
        } else {
          console.error('Ошибка загрузки товаров')
        }
      } catch (error) {
        console.error('Error loading products:', error)
        
        // Проверяем, если это ошибка авторизации
        if (error.message === 'Authentication failed') {
          this.$router.push('/login')
          return
        }
        
        console.error('Ошибка загрузки товаров')
      } finally {
        this.loading = false
      }
    },
    
    async loadSellers() {
      try {
        const response = await SellerAPI.getSellers()
        if (response.success) {
          this.sellers = Array.isArray(response.data) ? response.data : response.data.data
        }
      } catch (error) {
        console.error('Error loading sellers:', error)
        
        // Проверяем, если это ошибка авторизации
        if (error.message === 'Authentication failed') {
          this.$router.push('/login')
          return
        }
      }
    },
    
    async loadConsumers() {
      try {
        const response = await ConsumerAPI.getConsumers()
        if (response.success) {
          this.consumers = response.data
        } else {
          console.error('Ошибка загрузки потребителей')
        }
      } catch (error) {
        console.error('Error loading consumers:', error)
      }
    },
    
    searchProducts() {
      this.currentPage = 1
      this.loadProducts()
    },
    
    filterBySeller() {
      this.currentPage = 1
      this.loadProducts()
    },
    
    sortProducts() {
      this.currentPage = 1
      this.loadProducts()
    },
    
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
        this.loadProducts()
      }
    },
    
    openCreateModal() {
      this.selectedProduct = null
      this.showModal = true
    },
    
    editProduct(product) {
      // Устанавливаем selectedProduct
      this.selectedProduct = product
      // Скрываем форму просмотра
      this.showViewForm = false
      // Открываем форму редактирования
      this.showModal = true
    },
    
    async deleteProduct(product) {
      try {
        const response = await ProductAPI.deleteProduct(product.id)
        if (response.success) {
          console.log('Товар успешно удален')
          this.loadProducts()
        } else {
          console.error(response.message || 'Ошибка удаления товара')
        }
      } catch (error) {
        console.error('Error deleting product:', error)
        console.error('Ошибка удаления товара')
      }
    },
    
    closeModal() {
      this.showModal = false
      this.selectedProduct = null
    },
    
    viewProductDetails(product) {
      this.selectedProduct = product
      this.showViewForm = true
    },
    
    closeViewForm() {
      this.showViewForm = false
      // НЕ сбрасываем selectedProduct автоматически
      // this.selectedProduct = null
    },
    
    onProductSaved() {
      this.closeModal()
      this.loadProducts()
    },
    
    onSellerCreated(newSeller) {
      // Добавляем нового продавца в список
      this.sellers.push(newSeller)
    },
    
    onConsumerCreated(newConsumer) {
      // Добавляем нового потребителя в список
      this.consumers.push(newConsumer)
    },
    
    editSellerFromProduct(seller) {
      // Простое решение - открываем форму редактирования продавца
      // Пока что просто выводим в консоль
      console.log('Редактирование продавца:', seller)
      // TODO: Реализовать открытие формы редактирования продавца
    },
    
    onSellerUpdated() {
      // Обновляем списки после редактирования продавца
      this.loadSellers()
      this.loadProducts()
    },
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU')
    },
    
    async showProductClaims(product) {
      this.selectedProductForClaims = product
      this.showClaimsModal = true
      this.productClaimsLoading = true
      
      try {
        const response = await ClaimAPI.getClaimsByProduct(product.id)
        if (response.success) {
          this.productClaims = response.data
        } else {
          console.error('Ошибка загрузки претензий товара')
          this.productClaims = []
        }
      } catch (error) {
        console.error('Ошибка загрузки претензий товара:', error)
        this.productClaims = []
      } finally {
        this.productClaimsLoading = false
      }
    },
    
    closeClaimsModal() {
      this.showClaimsModal = false
      this.selectedProductForClaims = null
      this.productClaims = []
    },
    
    getStatusLabel(status) {
      const labels = {
        'pending': 'Ожидает рассмотрения',
        'in_progress': 'В работе',
        'resolved': 'Решена',
        'rejected': 'Отклонена'
      }
      return labels[status] || status
    },
    
    getTypeLabel(type) {
      const labels = {
        'defect': 'Брак',
        'warranty': 'Гарантия',
        'return': 'Возврат',
        'complaint': 'Жалоба'
      }
      return labels[type] || type
    },
    
    formatCurrency(amount) {
      return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
      }).format(amount)
    },
    
    async editClaimFromProduct(claim) {
      try {
        // Переходим на страницу претензий с открытой формой редактирования
        this.$router.push({
          path: '/claims',
          query: { edit: claim.id }
        })
        this.closeClaimsModal()
      } catch (error) {
        console.error('Ошибка при переходе к редактированию претензии:', error)
      }
    },
    
    async deleteClaimFromProduct(claim) {
      try {
        const response = await ClaimAPI.deleteClaim(claim.id)
        if (response.success) {
          console.log('Претензия успешно удалена')
          // Обновляем список претензий товара
          await this.showProductClaims(this.selectedProductForClaims)
          // Обновляем список товаров для обновления счетчиков
          this.loadProducts()
        } else {
          console.error('Ошибка удаления претензии:', response.message)
        }
      } catch (error) {
        console.error('Ошибка удаления претензии:', error)
      }
    }
  }
}
</script>

<style scoped>
.product-list {
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

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.product-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 20px 15px 20px;
  border-bottom: 1px solid #eee;
}

.product-title {
  margin: 0;
  font-size: 18px;
  color: #333;
  flex: 1;
}

.product-actions {
  display: flex;
  gap: 5px;
}

.product-actions .btn {
  min-width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.product-info {
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

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.purchase-date {
  font-size: 12px;
  color: #999;
}

.product-badges {
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

.badge-warning {
  background: #fff3cd;
  color: #856404;
  cursor: pointer;
  transition: background-color 0.2s;
}

.badge-warning:hover {
  background: #ffeaa7;
}

.badge-danger {
  background: #f8d7da;
  color: #721c24;
}

.no-data {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px;
  color: #666;
  font-size: 48px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
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

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-info:hover {
  background: #138496;
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

.product-card {
  cursor: pointer;
}

/* Всплывающее окно со списком претензий */
.claims-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.claims-popup {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.claims-popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.claims-popup-header h4 {
  margin: 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.claims-popup-body {
  padding: 20px;
  max-height: 400px;
  overflow-y: auto;
}

.claims-popup-footer {
  display: flex;
  justify-content: flex-end;
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.no-claims {
  text-align: center;
  padding: 40px;
  color: #666;
  font-size: 16px;
}

.claims-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.claim-item {
  background: #f8f9fa;
  border-radius: 6px;
  padding: 15px;
  border-left: 4px solid #007bff;
  position: relative;
}

.claim-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.claim-title {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.claim-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-in_progress {
  background: #cce5ff;
  color: #004085;
}

.status-resolved {
  background: #d4edda;
  color: #155724;
}

.status-rejected {
  background: #f8d7da;
  color: #721c24;
}

.claim-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-row .label {
  font-weight: 500;
  color: #666;
  font-size: 13px;
  min-width: 80px;
}

.info-row .value {
  color: #333;
  font-size: 13px;
  text-align: right;
  flex: 1;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #666;
  padding: 5px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background: #f5f5f5;
}

.claim-actions {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  justify-content: flex-end;
}

.claim-actions .btn {
  min-width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 15px;
  }
  
  .actions {
    align-self: flex-end;
  }
  
  .search-input {
    width: 100%;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .product-card {
    margin: 0 10px;
  }
  
  .product-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .product-actions {
    align-self: flex-end;
  }
  
  .claims-popup {
    width: 95%;
    margin: 20px;
  }
  
  .claims-popup-header,
  .claims-popup-body,
  .claims-popup-footer {
    padding: 15px;
  }
  
  .claim-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }
  
  .info-row .value {
    text-align: left;
  }
}
</style>
