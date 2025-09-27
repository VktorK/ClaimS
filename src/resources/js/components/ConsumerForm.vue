<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Редактировать потребителя' : 'Добавить потребителя' }}</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-row">
            <div class="form-group">
              <label for="last_name">Фамилия *</label>
              <input 
                id="last_name"
                v-model="form.last_name" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.last_name }"
                required
                placeholder="Введите фамилию"
              />
              <div v-if="errors.last_name" class="invalid-feedback">
                {{ errors.last_name[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="first_name">Имя *</label>
              <input 
                id="first_name"
                v-model="form.first_name" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.first_name }"
                required
                placeholder="Введите имя"
              />
              <div v-if="errors.first_name" class="invalid-feedback">
                {{ errors.first_name[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="middle_name">Отчество</label>
              <input 
                id="middle_name"
                v-model="form.middle_name" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.middle_name }"
                placeholder="Введите отчество"
              />
              <div v-if="errors.middle_name" class="invalid-feedback">
                {{ errors.middle_name[0] }}
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="address">Адрес *</label>
            <textarea 
              id="address"
              v-model="form.address" 
              class="form-control"
              :class="{ 'is-invalid': errors.address }"
              required
              placeholder="Введите полный адрес"
              rows="3"
            ></textarea>
            <div v-if="errors.address" class="invalid-feedback">
              {{ errors.address[0] }}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="passport">Паспорт *</label>
              <input 
                id="passport"
                v-model="form.passport" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.passport }"
                required
                placeholder="1234567890"
                maxlength="10"
                pattern="[0-9]{10}"
              />
              <div v-if="errors.passport" class="invalid-feedback">
                {{ errors.passport[0] }}
              </div>
              <small class="form-text text-muted">
                10 цифр серии и номера паспорта
              </small>
            </div>

            <div class="form-group">
              <label for="passport_issued_by">Кем выдан паспорт</label>
              <input 
                id="passport_issued_by"
                v-model="form.passport_issued_by" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.passport_issued_by }"
                placeholder="Например: ОТДЕЛЕНИЕМ УФМС РОССИИ"
                maxlength="70"
              />
              <div v-if="errors.passport_issued_by" class="invalid-feedback">
                {{ errors.passport_issued_by[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="passport_issued_date">Дата выдачи паспорта</label>
              <input 
                id="passport_issued_date"
                v-model="form.passport_issued_date" 
                type="date" 
                class="form-control"
                :class="{ 'is-invalid': errors.passport_issued_date }"
                :max="today"
              />
              <div v-if="errors.passport_issued_date" class="invalid-feedback">
                {{ errors.passport_issued_date[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="inn">ИНН</label>
              <input 
                id="inn"
                v-model="form.inn" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.inn }"
                placeholder="123456789012"
                maxlength="12"
                pattern="[0-9]{12}"
              />
              <div v-if="errors.inn" class="invalid-feedback">
                {{ errors.inn[0] }}
              </div>
              <small class="form-text text-muted">
                12 цифр ИНН (для физических лиц)
              </small>
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
  </div>
</template>

<script>
import { ConsumerAPI } from '../services/api.js'

export default {
  name: 'ConsumerForm',
  props: {
    consumer: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        middle_name: '',
        address: '',
        passport: '',
        passport_issued_by: '',
        passport_issued_date: '',
        inn: ''
      },
      errors: {},
      loading: false
    }
  },
  computed: {
    isEdit() {
      return !!this.consumer
    },
    
    today() {
      return new Date().toISOString().split('T')[0]
    }
  },
  watch: {
    consumer: {
      handler(newConsumer) {
        if (newConsumer) {
          this.fillForm()
        }
      },
      immediate: true
    }
  },
  methods: {
    fillForm() {
      this.form = {
        first_name: this.consumer.first_name || '',
        last_name: this.consumer.last_name || '',
        middle_name: this.consumer.middle_name || '',
        address: this.consumer.address || '',
        passport: this.consumer.passport || '',
        passport_issued_by: this.consumer.passport_issued_by || '',
        passport_issued_date: this.consumer.passport_issued_date || '',
        inn: this.consumer.inn || ''
      }
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        let response
        if (this.isEdit) {
          response = await ConsumerAPI.updateConsumer(this.consumer.id, this.form)
        } else {
          response = await ConsumerAPI.createConsumer(this.form)
        }
        
        if (response.success) {
          this.$emit('saved', response.data)
        } else {
          this.errors = response.errors || {}
        }
      } catch (error) {
        console.error('Ошибка при сохранении потребителя:', error)
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
        }
      } finally {
        this.loading = false
      }
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
  grid-template-columns: 1fr 1fr 1fr;
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
  width: 100%;
  margin-top: 5px;
  font-size: 12px;
  color: #dc3545;
}

.form-text {
  display: block;
  margin-top: 5px;
  font-size: 12px;
  color: #6c757d;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px 30px;
  border-top: 1px solid #eee;
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
