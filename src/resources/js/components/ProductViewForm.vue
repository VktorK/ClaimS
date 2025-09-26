<template>
  <div class="modal-overlay" @click="closeForm">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>Информация о товаре</h3>
        <button @click="closeForm" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div v-if="localProduct" class="product-view-form">
          <!-- Основная информация -->
          <div class="form-section">
            <h4>Основная информация</h4>
            
            <div class="form-group">
              <label for="title">Название товара</label>
              <input 
                id="title"
                :value="localProduct.title" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="localProduct.model">
              <label for="model">Модель</label>
              <input 
                id="model"
                :value="localProduct.model" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="localProduct.serial_number">
              <label for="serial_number">Серийный номер</label>
              <input 
                id="serial_number"
                :value="localProduct.serial_number" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="localProduct.price">
              <label for="price">Цена</label>
              <input 
                id="price"
                :value="localProduct.formatted_price" 
                type="text" 
                class="form-control price-field"
                readonly
              />
            </div>

            <div class="form-group">
              <label for="date_of_buying">Дата покупки</label>
              <input 
                id="date_of_buying"
                :value="formatDate(localProduct.date_of_buying)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="localProduct.seller">
              <label for="seller_title">Продавец</label>
              <input 
                id="seller_title"
                :value="localProduct.seller.title" 
                type="text" 
                class="form-control seller-field"
                readonly
                @click="showSellerDetails"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="editProduct" class="btn btn-warning">
          ✏️ Редактировать
        </button>
        <button type="button" @click="deleteProduct" class="btn btn-danger">
          🗑️ Удалить
        </button>
        <button type="button" @click="closeForm" class="btn btn-secondary">
          Закрыть
        </button>
      </div>
    </div>

    <!-- Всплывающее окно с данными продавца -->
    <div v-if="showSellerModal" class="seller-popup-overlay" @click="closeSellerModal">
      <div class="seller-popup" @click.stop>
        <div class="seller-popup-header">
          <h4>Информация о продавце</h4>
          <button @click="closeSellerModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="seller-popup-body" v-if="localProduct && localProduct.seller">
          <div class="seller-info">
            <div class="info-item">
              <label>Название:</label>
              <span>{{ localProduct.seller.title }}</span>
            </div>
            <div class="info-item" v-if="localProduct.seller.address">
              <label>Адрес:</label>
              <span>{{ localProduct.seller.address }}</span>
            </div>
            <div class="info-item" v-if="localProduct.seller.ogrn">
              <label>ОГРН:</label>
              <span>{{ localProduct.seller.ogrn }}</span>
            </div>
          </div>
        </div>
        <div class="seller-popup-footer">
          <button type="button" @click="editSeller" class="btn btn-warning btn-sm">
            ✏️ Редактировать
          </button>
          <button type="button" @click="closeSellerModal" class="btn btn-secondary btn-sm">
            Закрыть
          </button>
        </div>
      </div>
    </div>

    <!-- Форма редактирования продавца -->
    <SellerForm 
      v-if="showSellerEditForm"
      :seller="selectedSeller"
      @close="closeSellerEditForm"
      @saved="onSellerSaved"
    />
  </div>
</template>

<script>
import SellerForm from './SellerForm.vue'

export default {
  name: 'ProductViewForm',
  components: {
    SellerForm
  },
  props: {
    product: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'edit', 'delete', 'edit-seller', 'seller-updated'],
  data() {
    return {
      showSellerModal: false,
      showSellerEditForm: false,
      selectedSeller: null,
      localProduct: null
    }
  },
  watch: {
    product: {
      handler(newProduct) {
        if (newProduct) {
          this.localProduct = JSON.parse(JSON.stringify(newProduct))
        }
      },
      immediate: true
    }
  },
  methods: {
    closeForm() {
      this.$emit('close')
    },
    
    editProduct() {
      this.$emit('edit', this.localProduct)
      this.closeForm()
    },
    
    deleteProduct() {
      this.$emit('delete', this.localProduct)
      this.closeForm()
    },
    
    showSellerDetails() {
      this.showSellerModal = true
    },
    
    closeSellerModal() {
      this.showSellerModal = false
    },
    
    editSeller() {
      // Устанавливаем продавца для редактирования
      this.selectedSeller = this.localProduct.seller
      // Закрываем всплывающее окно
      this.showSellerModal = false
      // Открываем форму редактирования
      this.showSellerEditForm = true
    },
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU')
    },
    
    closeSellerEditForm() {
      this.showSellerEditForm = false
      this.selectedSeller = null
    },
    
    onSellerSaved() {
      this.closeSellerEditForm()
      // Обновляем локальную копию товара с обновленными данными продавца
      this.updateLocalProduct()
      // Эмитим событие для обновления данных в родительском компоненте
      this.$emit('seller-updated')
    },
    
    async updateLocalProduct() {
      if (this.localProduct && this.localProduct.id) {
        try {
          // Импортируем ProductAPI для получения обновленных данных
          const { ProductAPI } = await import('../services/api.js')
          const response = await ProductAPI.getProduct(this.localProduct.id)
          if (response.success) {
            this.localProduct = response.data
          }
        } catch (error) {
          console.error('Ошибка обновления данных товара:', error)
        }
      }
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
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.modal.large-modal {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #999;
  padding: 5px;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 15px;
}

.product-view-form {
  max-width: 100%;
}

.form-section {
  margin-bottom: 20px;
}

.form-section:last-child {
  margin-bottom: 0;
}

.form-section h4 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
  border-bottom: 2px solid #007bff;
  padding-bottom: 6px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #555;
  font-size: 13px;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background: #f8f9fa;
  color: #333;
  cursor: default;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.price-field {
  font-weight: 600;
  color: #28a745;
}

.seller-field {
  cursor: pointer;
  color: #007bff;
}

.seller-field:hover {
  background: #e3f2fd;
}

textarea.form-control {
  resize: vertical;
  min-height: 60px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 15px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
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

/* Стили для всплывающего окна продавца */
.seller-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.seller-popup {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 400px;
  position: relative;
}

.seller-popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
}

.seller-popup-header h4 {
  margin: 0;
  color: #333;
  font-size: 16px;
}

.seller-popup-body {
  padding: 15px;
}

.seller-popup-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 15px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.seller-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item label {
  font-weight: 600;
  color: #555;
  font-size: 12px;
}

.info-item span {
  color: #333;
  font-size: 14px;
  padding: 8px 10px;
  background: #f8f9fa;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

@media (max-width: 768px) {
  .modal {
    width: 95%;
    margin: 10px;
  }
  
  .modal-header {
    padding: 12px;
  }
  
  .modal-body {
    padding: 12px;
  }
  
  .modal-footer {
    flex-direction: column;
    gap: 8px;
  }
  
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
  
  .form-control {
    font-size: 16px; /* Предотвращает зум на iOS */
  }
  
  .seller-popup {
    width: 95%;
    margin: 10px;
  }
}
</style>
