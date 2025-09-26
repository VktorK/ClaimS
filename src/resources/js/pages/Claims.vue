<template>
  <div class="claims-page">
    <div class="page-header">
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-item">
          <i class="fas fa-home"></i>
          Главная
        </router-link>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-item active">Претензии</span>
      </div>
      
      <div class="page-title">
        <h1>Управление претензиями</h1>
        <p>Просмотр и управление всеми претензиями в системе</p>
      </div>
    </div>

    <div class="content-section">
      <div class="claims-content">
        <div class="claims-header">
          <h2>Список претензий</h2>
          <button @click="createClaim" class="btn btn-primary">
            Создать претензию
          </button>
        </div>

        <div v-if="loading" class="loading">
          ⏳ Загрузка претензий...
        </div>

        <div v-else>
          <div class="filters">
            <select v-model="statusFilter" @change="filterClaims" class="filter-select">
              <option value="">Все статусы</option>
              <option value="pending">Ожидает рассмотрения</option>
              <option value="in_progress">В работе</option>
              <option value="resolved">Решено</option>
              <option value="rejected">Отклонено</option>
            </select>
            
            <select v-model="typeFilter" @change="filterClaims" class="filter-select">
              <option value="">Все типы</option>
              <option value="defect">Брак</option>
              <option value="warranty">Гарантия</option>
              <option value="return">Возврат</option>
              <option value="complaint">Жалоба</option>
            </select>
          </div>

          <div class="claims-grid">
            <div v-for="claim in claims" :key="claim.id" class="claim-card">
              <div class="claim-header">
                <h3 class="claim-title">{{ claim.title }}</h3>
                <div class="claim-status" :class="'status-' + claim.status">
                  {{ getStatusLabel(claim.status) }}
                </div>
              </div>
              
              <div class="claim-info">
                <div class="info-item">
                  <span class="label">Тип:</span>
                  <span class="value">{{ getTypeLabel(claim.type) }}</span>
                </div>
                
                <div class="info-item" v-if="claim.product">
                  <span class="label">Товар:</span>
                  <span class="value">{{ claim.product.title }}</span>
                </div>
                
                <div class="info-item" v-if="claim.claimed_amount">
                  <span class="label">Сумма:</span>
                  <span class="value">{{ formatCurrency(claim.claimed_amount) }}</span>
                </div>
                
                <div class="info-item">
                  <span class="label">Дата подачи:</span>
                  <span class="value">{{ formatDate(claim.claim_date) }}</span>
                </div>
              </div>
              
              <div class="claim-description" v-if="claim.description">
                <p>{{ claim.description }}</p>
              </div>
              
              <div class="claim-actions">
                <button @click="viewClaim(claim)" class="btn btn-sm btn-info">
                  👁️ Просмотр
                </button>
                <button @click="editClaim(claim)" class="btn btn-sm btn-warning">
                  ✏️ Редактировать
                </button>
                <button @click="deleteClaim(claim)" class="btn btn-sm btn-danger">
                  🗑️ Удалить
                </button>
              </div>
            </div>
            
            <div v-if="claims.length === 0" class="no-data">
              📋
              <p>Претензии не найдены</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Форма создания/редактирования претензии -->
    <ClaimForm 
      v-if="showModal"
      :claim="selectedClaim"
      :products="products"
      @close="closeModal"
      @saved="onClaimSaved"
    />

    <!-- Форма просмотра претензии -->
    <ClaimViewForm 
      v-if="showViewForm"
      :claim="selectedClaim"
      @close="closeViewForm"
      @edit="editClaim"
      @delete="deleteClaim"
    />
  </div>
</template>

<script>
import { ClaimAPI, ProductAPI } from '../services/api.js'
import ClaimForm from '../components/ClaimForm.vue'
import ClaimViewForm from '../components/ClaimViewForm.vue'

export default {
  name: 'Claims',
  components: {
    ClaimForm,
    ClaimViewForm
  },
  data() {
    return {
      claims: [],
      products: [],
      loading: false,
      statusFilter: '',
      typeFilter: '',
      showModal: false,
      showViewForm: false,
      selectedClaim: null
    }
  },
  mounted() {
    this.loadClaims()
    this.loadProducts()
  },
  methods: {
    async loadClaims() {
      this.loading = true
      try {
        const params = {
          status: this.statusFilter,
          type: this.typeFilter
        }
        
        const response = await ClaimAPI.getClaims(params)
        
        if (response.success) {
          this.claims = Array.isArray(response.data) ? response.data : response.data.data
        } else {
          console.error('Ошибка загрузки претензий')
        }
      } catch (error) {
        console.error('Ошибка загрузки претензий:', error)
      } finally {
        this.loading = false
      }
    },
    
    async loadProducts() {
      try {
        const response = await ProductAPI.getProducts()
        if (response.success) {
          this.products = Array.isArray(response.data) ? response.data : response.data.data
        }
      } catch (error) {
        console.error('Ошибка загрузки товаров:', error)
      }
    },
    
    filterClaims() {
      this.loadClaims()
    },
    
    createClaim() {
      this.selectedClaim = null
      this.showModal = true
    },
    
    viewClaim(claim) {
      this.selectedClaim = claim
      this.showViewForm = true
    },
    
    editClaim(claim) {
      console.log('Claims: editClaim called with:', claim)
      this.selectedClaim = claim
      // Закрываем форму просмотра без сброса selectedClaim
      this.showViewForm = false
      // Используем nextTick для обеспечения правильной последовательности
      this.$nextTick(() => {
        console.log('Claims: opening modal with selectedClaim:', this.selectedClaim)
        this.showModal = true
      })
    },
    
    async deleteClaim(claim) {
      try {
        const response = await ClaimAPI.deleteClaim(claim.id)
        if (response.success) {
          console.log('Претензия успешно удалена')
          this.loadClaims()
        } else {
          console.error('Ошибка удаления претензии')
        }
      } catch (error) {
        console.error('Ошибка удаления претензии:', error)
      }
    },
    
    closeModal() {
      this.showModal = false
      // Не сбрасываем selectedClaim сразу, чтобы форма успела получить данные
      this.$nextTick(() => {
        this.selectedClaim = null
      })
    },
    
    closeViewForm() {
      this.showViewForm = false
      // Сбрасываем selectedClaim только если не открываем форму редактирования
      this.$nextTick(() => {
        if (!this.showModal) {
          this.selectedClaim = null
        }
      })
    },
    
    onClaimSaved() {
      this.closeModal()
      this.loadClaims()
    },
    
    getStatusLabel(status) {
      const labels = {
        'pending': 'Ожидает рассмотрения',
        'in_progress': 'В работе',
        'resolved': 'Решено',
        'rejected': 'Отклонено'
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
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU')
    }
  }
}
</script>

<style scoped>
.claims-page {
  padding: 20px;
}

.page-header {
  margin-bottom: 30px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  font-size: 14px;
}

.breadcrumb-item {
  color: #666;
  text-decoration: none;
}

.breadcrumb-item:hover {
  color: #007bff;
}

.breadcrumb-item.active {
  color: #333;
  font-weight: 500;
}

.breadcrumb-separator {
  color: #999;
}

.page-title h1 {
  margin: 0 0 8px 0;
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.page-title p {
  margin: 0;
  color: #666;
  font-size: 16px;
}

.content-section {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.claims-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #007bff;
}

.stat-icon {
  font-size: 24px;
  margin-right: 15px;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #333;
}

.stat-label {
  font-size: 14px;
  color: #666;
}


.claims-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.claims-header h2 {
  margin: 0;
  color: #333;
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
  font-size: 18px;
}

.claims-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.claim-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.claim-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.claim-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 20px 15px 20px;
  border-bottom: 1px solid #eee;
}

.claim-title {
  margin: 0;
  font-size: 18px;
  color: #333;
  flex: 1;
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
  padding: 15px 20px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item .label {
  color: #666;
  font-weight: 500;
}

.info-item .value {
  color: #333;
}

.claim-description {
  padding: 0 20px 15px 20px;
}

.claim-description p {
  margin: 0;
  color: #666;
  font-size: 14px;
  line-height: 1.4;
}

.claim-actions {
  display: flex;
  gap: 8px;
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.no-data {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px;
  color: #666;
  font-size: 48px;
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

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
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

@media (max-width: 768px) {
  .claims-page {
    padding: 10px;
  }
  
  .content-section {
    padding: 20px;
  }
  
  .claims-grid {
    grid-template-columns: 1fr;
  }
  
  .claims-header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
  
  .filters {
    flex-direction: column;
  }
  
  .claim-actions {
    flex-direction: column;
  }
}
</style>
