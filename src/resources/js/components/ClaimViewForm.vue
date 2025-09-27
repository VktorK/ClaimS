<template>
  <div class="modal-overlay" @click="closeForm">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>Информация о претензии</h3>
        <button @click="closeForm" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div v-if="claim" class="claim-view-form">
          <!-- Основная информация -->
          <div class="form-section">
            <h4>Основная информация</h4>
            
            <div class="form-group">
              <label for="type">Тип претензии</label>
              <input 
                id="type"
                :value="getTypeLabel(claim.type)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="status">Статус</label>
                <input 
                  id="status"
                  :value="getStatusLabel(claim.status)" 
                  type="text" 
                  class="form-control status-field"
                  :class="'status-' + claim.status"
                  readonly
                />
              </div>

              <div class="form-group" v-if="claim.claimed_amount">
                <label for="claimed_amount">Сумма претензии</label>
                <input 
                  id="claimed_amount"
                  :value="formatCurrency(claim.claimed_amount)" 
                  type="text" 
                  class="form-control"
                  readonly
                />
              </div>
            </div>

            <div class="form-group">
              <label for="claim_date">Дата подачи</label>
              <input 
                id="claim_date"
                :value="formatDate(claim.claim_date)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="claim.product">
              <label for="product_title">Товар</label>
              <input 
                id="product_title"
                :value="claim.product.title + (claim.product.model ? ` (${claim.product.model})` : '')" 
                type="text" 
                class="form-control product-field"
                readonly
                @click="showProductDetails"
              />
            </div>
          </div>

          <!-- Информация о ремонте -->
          <div class="form-section">
            <h4>Информация о ремонте</h4>
            
            <div class="form-group">
              <label>Был ли товар ранее в ремонте?</label>
              <input 
                :value="claim.was_in_repair ? 'Да' : 'Нет'" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div v-if="claim.was_in_repair && claim.service_center_documents" class="form-group">
              <label for="service_center_documents">Реквизиты документа из сервисного центра</label>
              <textarea 
                id="service_center_documents"
                :value="claim.service_center_documents" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>
            
            <div v-if="claim.was_in_repair && claim.previous_defect" class="form-group">
              <label for="previous_defect">С каким недостатком был ранее</label>
              <textarea 
                id="previous_defect"
                :value="claim.previous_defect" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>

            <div v-if="!claim.was_in_repair && claim.current_defect" class="form-group">
              <label for="current_defect">Какой недостаток был обнаружен</label>
              <textarea 
                id="current_defect"
                :value="claim.current_defect" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>
          </div>

          <!-- Информация об экспертизе -->
          <div class="form-section">
            <h4>Информация об экспертизе</h4>
            
            <div class="form-group">
              <label>Проводилась ли экспертиза или проверка качества?</label>
              <input 
                :value="claim.expertiseConducted ? 'Экспертиза (Проверка качества)' : 'Экспертиза (Проверка качества) не проводилась'" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div v-if="claim.expertiseConducted && claim.expertiseData" class="form-group">
              <label for="expertiseData">Данные о проведенной экспертизе</label>
              <textarea 
                id="expertiseData"
                :value="claim.expertiseData" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>
            
            <div v-if="claim.expertiseConducted && claim.expertiseDefect" class="form-group">
              <label for="expertiseDefect">Недостаток согласно экспертизе</label>
              <textarea 
                id="expertiseDefect"
                :value="claim.expertiseDefect" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>

            <div v-if="!claim.expertiseConducted && claim.actualDefect" class="form-group">
              <label for="actualDefect">Настоящий недостаток</label>
              <textarea 
                id="actualDefect"
                :value="claim.actualDefect" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>
          </div>

          <!-- Информация о решении -->
          <div class="form-section" v-if="claim.resolution_date || claim.resolution_notes">
            <h4>Информация о решении</h4>
            
            <div class="form-group" v-if="claim.resolution_date">
              <label for="resolution_date">Дата решения</label>
              <input 
                id="resolution_date"
                :value="formatDate(claim.resolution_date)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group" v-if="claim.resolution_notes">
              <label for="resolution_notes">Примечания к решению</label>
              <textarea 
                id="resolution_notes"
                :value="claim.resolution_notes" 
                class="form-control"
                readonly
                rows="3"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="editClaim" class="btn btn-warning">
          ✏️ Редактировать
        </button>
        <button type="button" @click="deleteClaim" class="btn btn-danger">
          🗑️ Удалить
        </button>
        <button type="button" @click="closeForm" class="btn btn-secondary">
          Закрыть
        </button>
      </div>
    </div>

    <!-- Всплывающее окно с данными товара -->
    <div v-if="showProductModal" class="product-popup-overlay" @click="closeProductModal">
      <div class="product-popup" @click.stop>
        <div class="product-popup-header">
          <h4>Информация о товаре</h4>
          <button @click="closeProductModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="product-popup-body" v-if="claim && claim.product">
          <div class="product-info">
            <div class="info-item">
              <label>Название:</label>
              <span>{{ claim.product.title }}</span>
            </div>
            <div class="info-item" v-if="claim.product.model">
              <label>Модель:</label>
              <span>{{ claim.product.model }}</span>
            </div>
            <div class="info-item" v-if="claim.product.serial_number">
              <label>Серийный номер:</label>
              <span>{{ claim.product.serial_number }}</span>
            </div>
            <div class="info-item" v-if="claim.product.price">
              <label>Цена:</label>
              <span>{{ formatCurrency(claim.product.price) }}</span>
            </div>
          </div>
        </div>
        <div class="product-popup-footer">
          <button type="button" @click="closeProductModal" class="btn btn-secondary btn-sm">
            Закрыть
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClaimViewForm',
  props: {
    claim: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'edit', 'delete'],
  data() {
    return {
      showProductModal: false
    }
  },
  methods: {
    closeForm() {
      this.$emit('close')
    },
    
    editClaim() {
      this.$emit('edit', this.claim)
      this.closeForm()
    },
    
    deleteClaim() {
      this.$emit('delete', this.claim)
      this.closeForm()
    },
    
    showProductDetails() {
      this.showProductModal = true
    },
    
    closeProductModal() {
      this.showProductModal = false
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
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU')
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.large-modal {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
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

.modal-body {
  padding: 30px;
}

.claim-view-form {
  font-size: 14px;
}

.form-section {
  margin-bottom: 30px;
}

.form-section:last-child {
  margin-bottom: 0;
}

.form-section h4 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
  border-bottom: 2px solid #007bff;
  padding-bottom: 8px;
}

.form-group {
  margin-bottom: 15px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #333;
  font-size: 13px;
}

.form-control {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
  background: #f8f9fa;
  color: #333;
}

.status-field {
  font-weight: 500;
}

.status-pending {
  background: #fff3cd !important;
  color: #856404 !important;
}

.status-in_progress {
  background: #cce5ff !important;
  color: #004085 !important;
}

.status-resolved {
  background: #d4edda !important;
  color: #155724 !important;
}

.status-rejected {
  background: #f8d7da !important;
  color: #721c24 !important;
}

.product-field {
  cursor: pointer;
  color: #007bff;
}

.product-field:hover {
  background: #e3f2fd !important;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px 30px;
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
  gap: 5px;
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

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

/* Всплывающее окно товара */
.product-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.product-popup {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.product-popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.product-popup-header h4 {
  margin: 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.product-popup-body {
  padding: 20px;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item label {
  font-weight: 500;
  color: #666;
  font-size: 13px;
  min-width: 120px;
}

.info-item span {
  color: #333;
  font-size: 13px;
  text-align: right;
  flex: 1;
}

.product-popup-footer {
  display: flex;
  justify-content: flex-end;
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

textarea.form-control {
  resize: vertical;
  min-height: 60px;
}

@media (max-width: 768px) {
  .modal {
    width: 95%;
    margin: 20px;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
  
  .product-popup {
    width: 95%;
    margin: 20px;
  }
  
  .product-popup-header,
  .product-popup-body,
  .product-popup-footer {
    padding: 15px;
  }
}
</style>
