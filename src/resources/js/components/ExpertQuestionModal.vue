<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h3>Экспертиза товара</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>
      
      <div class="modal-body">
        <div class="question-section">
          <h4>Проводилась ли экспертиза или проверка качества?</h4>
          
          <div class="radio-group">
            <label class="radio-option">
              <input 
                type="radio" 
                v-model="expertiseConducted" 
                value="yes"
                :disabled="!canSelectNoExpertise"
              />
              <span class="radio-label">Экспертиза (Проверка качества)</span>
            </label>
            
            <label class="radio-option">
              <input 
                type="radio" 
                v-model="expertiseConducted" 
                value="no"
                :disabled="!canSelectNoExpertise"
              />
              <span class="radio-label">Экспертиза (Проверка качества) не проводилась</span>
            </label>
          </div>
          
          <div v-if="!canSelectNoExpertise && expertiseConducted === 'no'" class="warning-message">
            ⚠️ Этот вариант доступен только если гарантийный срок товара ещё не закончился или не был указан
          </div>
        </div>
        
        <div v-if="expertiseConducted === 'yes'" class="form-section">
          <div class="form-group">
            <label for="expertiseData">Данные о проведенной экспертизе</label>
            <textarea 
              id="expertiseData"
              v-model="expertiseData" 
              class="form-control"
              placeholder="Укажите данные о проведенной экспертизе"
              rows="3"
            ></textarea>
          </div>
          
          <div class="form-group">
            <label for="expertiseDefect">Недостаток согласно экспертизе</label>
            <textarea 
              id="expertiseDefect"
              v-model="expertiseDefect" 
              class="form-control"
              placeholder="Опишите недостаток согласно экспертизе"
              rows="3"
            ></textarea>
          </div>
        </div>
        
        <div v-if="expertiseConducted === 'no'" class="form-section">
          <div class="form-group">
            <label for="actualDefect">Укажите настоящий недостаток</label>
            <textarea 
              id="actualDefect"
              v-model="actualDefect" 
              class="form-control"
              placeholder="Опишите настоящий недостаток товара"
              rows="3"
            ></textarea>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" @click="closeModal" class="btn btn-secondary">
          Отмена
        </button>
        <button type="button" @click="saveAndContinue" class="btn btn-primary" :disabled="!isFormValid">
          Продолжить
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ExpertQuestionModal',
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      expertiseConducted: '',
      expertiseData: '',
      expertiseDefect: '',
      actualDefect: ''
    }
  },
  computed: {
    canSelectNoExpertise() {
      if (!this.product) return true
      
      // Если гарантийный срок не указан, можно выбрать "не проводилась"
      if (!this.product.warranty_period) return true
      
      // Проверяем, не истёк ли гарантийный срок
      if (!this.product.date_of_buying) return true
      
      const purchaseDate = new Date(this.product.date_of_buying)
      const warrantyEndDate = new Date(purchaseDate)
      warrantyEndDate.setMonth(warrantyEndDate.getMonth() + this.product.warranty_period)
      
      return warrantyEndDate >= new Date()
    },
    
    isFormValid() {
      if (!this.expertiseConducted) return false
      
      if (this.expertiseConducted === 'yes') {
        return this.expertiseData.trim() && this.expertiseDefect.trim()
      }
      
      if (this.expertiseConducted === 'no') {
        return this.actualDefect.trim()
      }
      
      return false
    }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    },
    
    saveAndContinue() {
      if (!this.isFormValid) return
      
      const data = {
        expertiseConducted: this.expertiseConducted === 'yes',
        expertiseData: this.expertiseData,
        expertiseDefect: this.expertiseDefect,
        actualDefect: this.actualDefect
      }
      
      this.$emit('save', data)
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
  z-index: 2000;
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

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.question-section {
  margin-bottom: 20px;
}

.question-section h4 {
  margin-bottom: 15px;
  color: #333;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.radio-option {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 10px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.radio-option:hover {
  background-color: #f8f9fa;
}

.radio-option input[type="radio"] {
  margin-right: 10px;
}

.radio-option input[type="radio"]:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.radio-option:has(input[type="radio"]:disabled) {
  opacity: 0.5;
  cursor: not-allowed;
}

.radio-label {
  font-size: 14px;
  color: #333;
}

.warning-message {
  margin-top: 10px;
  padding: 10px;
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  color: #856404;
  font-size: 14px;
}

.form-section {
  margin-top: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #e9ecef;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-primary:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
</style>
