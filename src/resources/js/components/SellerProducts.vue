<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h3>Товары продавца</h3>
        <button @click="closeModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="seller-info">
          <h4>{{ seller.title }}</h4>
          <div class="seller-details">
            <div v-if="seller.address" class="detail-item">
              <i class="fas fa-map-marker-alt"></i>
              <span>{{ seller.address }}</span>
            </div>
            <div v-if="seller.ogrn" class="detail-item">
              <i class="fas fa-certificate"></i>
              <span>ОГРН: {{ seller.ogrn }}</span>
            </div>
          </div>
        </div>

        <div v-if="loading" class="loading">
          <i class="fas fa-spinner fa-spin"></i>
          Загрузка товаров...
        </div>

        <div v-else>
          <div class="products-summary">
            <div class="summary-item">
              <span class="label">Всего товаров:</span>
              <span class="value">{{ products.length }}</span>
            </div>
            <div class="summary-item">
              <span class="label">Общая стоимость:</span>
              <span class="value">{{ formatCurrency(totalValue) }}</span>
            </div>
          </div>

          <div class="products-list">
            <div v-for="product in products" :key="product.id" class="product-item">
              <div class="product-main">
                <h5 class="product-title">{{ product.title }}</h5>
                <div class="product-price">{{ product.formatted_price || formatCurrency(product.price) }}</div>
              </div>
              
              <div class="product-details">
                <div v-if="product.model" class="product-detail">
                  <span class="detail-label">Модель:</span>
                  <span>{{ product.model }}</span>
                </div>
                <div v-if="product.serial_number" class="product-detail">
                  <span class="detail-label">Серийный номер:</span>
                  <span>{{ product.serial_number }}</span>
                </div>
                <div v-if="product.date_of_buying" class="product-detail">
                  <span class="detail-label">Дата покупки:</span>
                  <span>{{ formatDate(product.date_of_buying) }}</span>
                </div>
              </div>
            </div>
            
            <div v-if="products.length === 0" class="no-products">
              <i class="fas fa-box-open"></i>
              <p>У этого продавца пока нет продуктов</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="closeModal" class="btn btn-secondary">
          Закрыть
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { SellerAPI } from '../services/api.js'

export default {
  name: 'SellerProducts',
  props: {
    seller: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  data() {
    return {
      products: [],
      loading: false
    }
  },
  computed: {
    totalValue() {
      return this.products.reduce((sum, product) => {
        return sum + (parseFloat(product.price) || 0)
      }, 0)
    }
  },
  mounted() {
    this.loadProducts()
  },
  methods: {
    async loadProducts() {
      this.loading = true
      try {
        const response = await SellerAPI.getSellerProducts(this.seller.id)
        if (response.success) {
          this.products = response.data.products || []
        } else {
          this.$toast.error('Ошибка загрузки продуктов')
        }
      } catch (error) {
        console.error('Error loading seller products:', error)
        this.$toast.error('Ошибка загрузки продуктов')
      } finally {
        this.loading = false
      }
    },
    
    closeModal() {
      this.$emit('close')
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
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #666;
  padding: 5px;
  border-radius: 4px;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #333;
}

.modal-body {
  padding: 20px;
}

.seller-info {
  margin-bottom: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 6px;
}

.seller-info h4 {
  margin: 0 0 10px 0;
  color: #333;
}

.seller-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  font-size: 14px;
}

.detail-item i {
  width: 16px;
  color: #999;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.loading i {
  font-size: 24px;
  margin-right: 10px;
}

.products-summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
  padding: 15px;
  background: #e3f2fd;
  border-radius: 6px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-item .label {
  font-weight: 500;
  color: #555;
}

.summary-item .value {
  font-weight: 600;
  color: #333;
}

.products-list {
  max-height: 400px;
  overflow-y: auto;
}

.product-item {
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 6px;
  margin-bottom: 10px;
  transition: box-shadow 0.2s;
}

.product-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.product-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.product-title {
  margin: 0;
  color: #333;
  font-size: 16px;
  flex: 1;
}

.product-price {
  font-weight: 600;
  color: #28a745;
  font-size: 16px;
  margin-left: 15px;
}

.product-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
}

.product-detail {
  font-size: 13px;
  color: #666;
}

.detail-label {
  font-weight: 500;
  margin-right: 5px;
}

.no-products {
  text-align: center;
  padding: 40px;
  color: #666;
}

.no-products i {
  font-size: 48px;
  margin-bottom: 10px;
  opacity: 0.3;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
  margin: 0 -20px -20px -20px;
  border-radius: 0 0 8px 8px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

@media (max-width: 768px) {
  .modal {
    width: 95%;
    margin: 20px;
  }
  
  .products-summary {
    grid-template-columns: 1fr;
  }
  
  .product-main {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  
  .product-price {
    margin-left: 0;
  }
  
  .product-details {
    grid-template-columns: 1fr;
  }
}
</style>
