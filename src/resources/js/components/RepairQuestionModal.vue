<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h3>Вопрос о ремонте</h3>
        <button @click="closeModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="question-section">
          <h4>Был ли товар ранее в ремонте?</h4>
          
          <div class="radio-group">
            <label class="radio-label">
              <input 
                type="radio" 
                v-model="wasInRepair" 
                :value="true"
                @change="onRepairStatusChange"
              />
              <span class="radio-text">Да</span>
            </label>
            <label class="radio-label">
              <input 
                type="radio" 
                v-model="wasInRepair" 
                :value="false"
                @change="onRepairStatusChange"
              />
              <span class="radio-text">Нет</span>
            </label>
          </div>
        </div>

        <!-- Поля для случая "Да" (был в ремонте) -->
        <div v-if="wasInRepair === true" class="repair-fields">
          <div class="form-group">
            <label for="service_center_documents">Реквизиты документов из сервисного центра</label>
            <textarea 
              id="service_center_documents"
              v-model="serviceCenterDocuments" 
              class="form-control"
              placeholder="Введите реквизиты документов из сервисного центра"
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="previous_defect">С каким недостатком был ранее</label>
            <textarea 
              id="previous_defect"
              v-model="previousDefect" 
              class="form-control"
              placeholder="Укажите с каким недостатком был ранее"
              rows="3"
            ></textarea>
          </div>
        </div>

        <!-- Поля для случая "Нет" (не был в ремонте) -->
        <div v-if="wasInRepair === false" class="defect-fields">
          <div class="form-group">
            <label for="current_defect">Какой недостаток был обнаружен</label>
            <textarea 
              id="current_defect"
              v-model="currentDefect" 
              class="form-control"
              placeholder="Укажите какой недостаток был обнаружен"
              rows="3"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="closeModal" class="btn btn-secondary">
          Отмена
        </button>
        <button type="button" @click="saveAndContinue" class="btn btn-primary" :disabled="wasInRepair === null">
          Продолжить
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RepairQuestionModal',
  emits: ['close', 'save'],
  data() {
    return {
      wasInRepair: null,
      serviceCenterDocuments: '',
      previousDefect: '',
      currentDefect: ''
    }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    },
    
    onRepairStatusChange() {
      // Очищаем поля при изменении статуса ремонта
      if (this.wasInRepair === true) {
        this.currentDefect = ''
      } else if (this.wasInRepair === false) {
        this.serviceCenterDocuments = ''
        this.previousDefect = ''
      }
    },
    
    saveAndContinue() {
      if (this.wasInRepair === null) return
      
      const repairData = {
        was_in_repair: this.wasInRepair,
        service_center_documents: this.serviceCenterDocuments,
        previous_defect: this.previousDefect,
        current_defect: this.currentDefect
      }
      
      this.$emit('save', repairData)
      this.closeModal()
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

.question-section {
  margin-bottom: 30px;
}

.question-section h4 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.radio-group {
  display: flex;
  gap: 30px;
  margin-top: 15px;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-weight: normal;
  padding: 10px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.2s;
}

.radio-label:hover {
  border-color: #007bff;
  background: #f8f9fa;
}

.radio-label input[type="radio"] {
  margin: 0;
  transform: scale(1.2);
}

.radio-label input[type="radio"]:checked + .radio-text {
  color: #007bff;
  font-weight: 600;
}

.radio-text {
  font-size: 16px;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.repair-fields,
.defect-fields {
  margin-top: 20px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #007bff;
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
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
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
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
  
  .radio-group {
    flex-direction: column;
    gap: 15px;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
