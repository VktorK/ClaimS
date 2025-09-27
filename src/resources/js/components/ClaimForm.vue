<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Редактировать претензию' : 'Создать претензию' }}</h3>
        <button @click="closeModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="product_id">Товар *</label>
            <select 
              id="product_id"
              v-model="form.product_id" 
              class="form-control"
              :class="{ 'is-invalid': errors.product_id }"
              required
              @change="onProductChange"
            >
              <option value="">Выберите товар</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.title }} {{ product.model ? `(${product.model})` : '' }}
              </option>
            </select>
            <div v-if="errors.product_id" class="invalid-feedback">
              {{ errors.product_id[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="type">Тип претензии *</label>
            <select 
              id="type"
              v-model="form.type" 
              class="form-control"
              :class="{ 'is-invalid': errors.type }"
              required
            >
              <option value="">Выберите тип</option>
              <option value="defect">Брак</option>
              <option value="warranty">Гарантия</option>
              <option value="return">Возврат</option>
              <option value="complaint">Жалоба</option>
            </select>
            <div v-if="errors.type" class="invalid-feedback">
              {{ errors.type[0] }}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" v-if="isEdit">
              <label for="status">Статус *</label>
              <select 
                id="status"
                v-model="form.status" 
                class="form-control"
                :class="{ 'is-invalid': errors.status }"
                required
              >
                <option value="pending">Ожидает рассмотрения</option>
                <option value="in_progress">В работе</option>
                <option value="resolved">Решена</option>
                <option value="rejected">Отклонена</option>
              </select>
              <div v-if="errors.status" class="invalid-feedback">
                {{ errors.status[0] }}
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="claimed_amount">Сумма претензии</label>
              <input 
                id="claimed_amount"
                v-model="form.claimed_amount" 
                type="number" 
                step="0.01"
                min="0"
                class="form-control"
                :class="{ 'is-invalid': errors.claimed_amount }"
                placeholder="0.00"
              />
              <div v-if="errors.claimed_amount" class="invalid-feedback">
                {{ errors.claimed_amount[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="claim_date">Дата подачи *</label>
              <input 
                id="claim_date"
                v-model="form.claim_date" 
                type="date" 
                class="form-control"
                :class="{ 'is-invalid': errors.claim_date }"
                required
              />
              <div v-if="errors.claim_date" class="invalid-feedback">
                {{ errors.claim_date[0] }}
              </div>
            </div>
          </div>

          <div class="form-row" v-if="isEdit">
            <div class="form-group">
              <label for="resolution_date">Дата решения</label>
              <input 
                id="resolution_date"
                v-model="form.resolution_date" 
                type="date" 
                class="form-control"
                :class="{ 'is-invalid': errors.resolution_date }"
              />
              <div v-if="errors.resolution_date" class="invalid-feedback">
                {{ errors.resolution_date[0] }}
              </div>
            </div>
          </div>

          <div class="form-group" v-if="isEdit">
            <label for="resolution_notes">Примечания к решению</label>
            <textarea 
              id="resolution_notes"
              v-model="form.resolution_notes" 
              class="form-control"
              :class="{ 'is-invalid': errors.resolution_notes }"
              placeholder="Опишите решение по претензии"
              rows="3"
            ></textarea>
            <div v-if="errors.resolution_notes" class="invalid-feedback">
              {{ errors.resolution_notes[0] }}
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              Отмена
            </button>
            <button type="submit" :disabled="loading" class="btn btn-primary">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i>
              {{ isEdit ? 'Обновить' : 'Создать' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Модальное окно с вопросом о ремонте -->
    <RepairQuestionModal 
      v-if="showRepairModal"
      @close="closeRepairModal"
      @save="onRepairDataSaved"
    />
  </div>
</template>

<script>
import { ClaimAPI, ProductAPI } from '../services/api.js'
import RepairQuestionModal from './RepairQuestionModal.vue'

export default {
  name: 'ClaimForm',
  components: {
    RepairQuestionModal
  },
  props: {
    claim: {
      type: Object,
      default: null
    },
    products: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      form: {
        product_id: '',
        type: '',
        status: 'pending',
        was_in_repair: null,
        service_center_documents: '',
        previous_defect: '',
        current_defect: '',
        claimed_amount: '',
        claim_date: '',
        resolution_date: '',
        resolution_notes: ''
      },
      errors: {},
      loading: false,
      showRepairModal: false
    }
  },
  computed: {
    isEdit() {
      return !!this.claim
    }
  },
  watch: {
    claim: {
      handler(newClaim) {
        console.log('ClaimForm: claim prop changed:', newClaim)
        console.log('ClaimForm: isEdit will be:', !!newClaim)
        if (newClaim) {
          this.fillForm()
        }
      },
      immediate: true
    }
  },
  mounted() {
    if (this.claim) {
      this.fillForm()
    } else {
      // Устанавливаем сегодняшнюю дату по умолчанию
      this.form.claim_date = new Date().toISOString().split('T')[0]
    }
  },
  methods: {
    fillForm() {
      console.log('ClaimForm: fillForm called with claim:', this.claim)
      this.form = {
        product_id: this.claim.product_id || '',
        type: this.claim.type || '',
        status: this.claim.status || 'pending',
        was_in_repair: this.claim.was_in_repair ?? null,
        service_center_documents: this.claim.service_center_documents || '',
        previous_defect: this.claim.previous_defect || '',
        current_defect: this.claim.current_defect || '',
        claimed_amount: this.claim.claimed_amount || '',
        claim_date: this.claim.claim_date || '',
        resolution_date: this.claim.resolution_date || '',
        resolution_notes: this.claim.resolution_notes || ''
      }
      console.log('ClaimForm: form filled with:', this.form)
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        let response
        if (this.isEdit) {
          response = await ClaimAPI.updateClaim(this.claim.id, this.form)
        } else {
          response = await ClaimAPI.createClaim(this.form)
        }
        
        if (response.success) {
          console.log(
            this.isEdit ? 'Претензия успешно обновлена' : 'Претензия успешно создана'
          )
          this.$emit('saved', response.data)
        } else {
          if (response.errors) {
            this.errors = response.errors
          } else {
            console.error(response.message || 'Ошибка сохранения претензии')
          }
        }
      } catch (error) {
        console.error('Error saving claim:', error)
        console.error('Ошибка сохранения претензии')
      } finally {
        this.loading = false
      }
    },
    
    onProductChange() {
      // При изменении товара показываем модальное окно с вопросом о ремонте
      if (this.form.product_id && !this.isEdit) {
        this.showRepairModal = true
      }
    },
    
    onRepairDataSaved(repairData) {
      // Сохраняем данные о ремонте в форму
      this.form.was_in_repair = repairData.was_in_repair
      this.form.service_center_documents = repairData.service_center_documents
      this.form.previous_defect = repairData.previous_defect
      this.form.current_defect = repairData.current_defect
      this.showRepairModal = false
    },
    
    closeRepairModal() {
      this.showRepairModal = false
    },
    
    closeModal() {
      this.$emit('close')
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

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
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
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 12px;
  margin-top: 4px;
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
}
</style>
