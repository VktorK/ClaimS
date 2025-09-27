<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Редактировать товар' : 'Добавить товар' }}</h3>
        <button @click="closeModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="title">Название *</label>
            <input 
              id="title"
              v-model="form.title" 
              type="text" 
              class="form-control"
              :class="{ 'is-invalid': errors.title }"
              placeholder="Введите название товара"
              required
            />
            <div v-if="errors.title" class="invalid-feedback">
              {{ errors.title[0] }}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="model">Модель *</label>
              <input 
                id="model"
                v-model="form.model" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.model }"
                placeholder="Модель товара"
                required
              />
              <div v-if="errors.model" class="invalid-feedback">
                {{ errors.model[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="serial_number">Серийный номер</label>
              <input 
                id="serial_number"
                v-model="form.serial_number" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': errors.serial_number }"
                placeholder="Серийный номер"
              />
              <div v-if="errors.serial_number" class="invalid-feedback">
                {{ errors.serial_number[0] }}
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="price">Цена *</label>
              <input 
                id="price"
                v-model.number="form.price" 
                type="number" 
                step="0.01"
                min="0"
                class="form-control"
                :class="{ 'is-invalid': errors.price }"
                placeholder="0.00"
                required
              />
              <div v-if="errors.price" class="invalid-feedback">
                {{ errors.price[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="date_of_buying">Дата покупки *</label>
              <input 
                id="date_of_buying"
                v-model="form.date_of_buying" 
                type="date" 
                class="form-control"
                :class="{ 'is-invalid': errors.date_of_buying }"
                :max="today"
                required
              />
              <div v-if="errors.date_of_buying" class="invalid-feedback">
                {{ errors.date_of_buying[0] }}
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="warranty_period">Срок гарантии (месяцы)</label>
            <input 
              id="warranty_period"
              v-model.number="form.warranty_period" 
              type="number" 
              min="1"
              max="120"
              class="form-control"
              :class="{ 'is-invalid': errors.warranty_period }"
              placeholder="Например: 12"
            />
            <div v-if="errors.warranty_period" class="invalid-feedback">
              {{ errors.warranty_period[0] }}
            </div>
            <small class="form-text text-muted">
              Укажите срок гарантии в месяцах (от 1 до 120)
            </small>
          </div>

          <div class="form-group">
            <label for="consumer_id">Потребитель</label>
            <div class="consumer-input-group">
              <select 
                id="consumer_id"
                v-model="form.consumer_id" 
                class="form-control"
                :class="{ 'is-invalid': errors.consumer_id }"
              >
                <option value="">Выберите потребителя</option>
                <option v-for="consumer in consumers" :key="consumer.id" :value="consumer.id">
                  {{ consumer?.full_name || 'Не указано' }}
                </option>
              </select>
              <button type="button" @click="openConsumerModal" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus"></i>
                Создать
              </button>
            </div>
            <div v-if="errors.consumer_id" class="invalid-feedback">
              {{ errors.consumer_id[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="seller_id">Продавец *</label>
            <div class="seller-input-group">
              <select 
                id="seller_id"
                v-model="form.seller_id" 
                class="form-control"
                :class="{ 'is-invalid': errors.seller_id }"
                required
              >
                <option value="">Выберите продавца</option>
                <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                  {{ seller.title }}
                </option>
              </select>
              <button type="button" @click="openSellerModal" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus"></i>
                Создать
              </button>
            </div>
            <div v-if="errors.seller_id" class="invalid-feedback">
              {{ errors.seller_id[0] }}
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

    <!-- Modal для создания продавца -->
    <div v-if="showSellerModal" class="modal-overlay" @click="closeSellerModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Создать продавца</h3>
          <button @click="closeSellerModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="submitSellerForm">
            <div class="form-group">
              <label for="seller_title">Название *</label>
              <input 
                id="seller_title"
                v-model="sellerForm.title" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': sellerErrors.title }"
                placeholder="Название продавца"
                required
              />
              <div v-if="sellerErrors.title" class="invalid-feedback">
                {{ sellerErrors.title[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="seller_address">Адрес *</label>
              <input 
                id="seller_address"
                v-model="sellerForm.address" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': sellerErrors.address }"
                placeholder="Адрес продавца"
                required
              />
              <div v-if="sellerErrors.address" class="invalid-feedback">
                {{ sellerErrors.address[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="seller_ogrn">ОГРН *</label>
              <input 
                id="seller_ogrn"
                v-model="sellerForm.ogrn" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': sellerErrors.ogrn }"
                placeholder="ОГРН"
                required
              />
              <div v-if="sellerErrors.ogrn" class="invalid-feedback">
                {{ sellerErrors.ogrn[0] }}
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeSellerModal" class="btn btn-secondary">
                Отмена
              </button>
              <button type="submit" :disabled="sellerLoading" class="btn btn-primary">
                <i v-if="sellerLoading" class="fas fa-spinner fa-spin"></i>
                Создать
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal для создания потребителя -->
    <div v-if="showConsumerModal" class="modal-overlay" @click="closeConsumerModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Создать потребителя</h3>
          <button @click="closeConsumerModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="submitConsumerForm">
            <div class="form-row">
              <div class="form-group">
                <label for="consumer_last_name">Фамилия *</label>
                <input 
                  id="consumer_last_name"
                  v-model="consumerForm.last_name" 
                  type="text" 
                  class="form-control"
                  :class="{ 'is-invalid': consumerErrors.last_name }"
                  placeholder="Фамилия"
                  required
                />
                <div v-if="consumerErrors.last_name" class="invalid-feedback">
                  {{ consumerErrors.last_name[0] }}
                </div>
              </div>

              <div class="form-group">
                <label for="consumer_first_name">Имя *</label>
                <input 
                  id="consumer_first_name"
                  v-model="consumerForm.first_name" 
                  type="text" 
                  class="form-control"
                  :class="{ 'is-invalid': consumerErrors.first_name }"
                  placeholder="Имя"
                  required
                />
                <div v-if="consumerErrors.first_name" class="invalid-feedback">
                  {{ consumerErrors.first_name[0] }}
                </div>
              </div>

              <div class="form-group">
                <label for="consumer_middle_name">Отчество</label>
                <input 
                  id="consumer_middle_name"
                  v-model="consumerForm.middle_name" 
                  type="text" 
                  class="form-control"
                  :class="{ 'is-invalid': consumerErrors.middle_name }"
                  placeholder="Отчество"
                />
                <div v-if="consumerErrors.middle_name" class="invalid-feedback">
                  {{ consumerErrors.middle_name[0] }}
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="consumer_address">Адрес *</label>
              <textarea 
                id="consumer_address"
                v-model="consumerForm.address" 
                class="form-control"
                :class="{ 'is-invalid': consumerErrors.address }"
                placeholder="Полный адрес"
                rows="3"
                required
              ></textarea>
              <div v-if="consumerErrors.address" class="invalid-feedback">
                {{ consumerErrors.address[0] }}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="consumer_passport">Паспорт *</label>
                <input 
                  id="consumer_passport"
                  v-model="consumerForm.passport" 
                  type="text" 
                  class="form-control"
                  :class="{ 'is-invalid': consumerErrors.passport }"
                  placeholder="1234567890"
                  maxlength="10"
                  pattern="[0-9]{10}"
                  required
                />
                <div v-if="consumerErrors.passport" class="invalid-feedback">
                  {{ consumerErrors.passport[0] }}
                </div>
                <small class="form-text text-muted">
                  10 цифр серии и номера паспорта
                </small>
              </div>

              <div class="form-group">
                <label for="consumer_inn">ИНН</label>
                <input 
                  id="consumer_inn"
                  v-model="consumerForm.inn" 
                  type="text" 
                  class="form-control"
                  :class="{ 'is-invalid': consumerErrors.inn }"
                  placeholder="123456789012"
                  maxlength="12"
                  pattern="[0-9]{12}"
                />
                <div v-if="consumerErrors.inn" class="invalid-feedback">
                  {{ consumerErrors.inn[0] }}
                </div>
                <small class="form-text text-muted">
                  12 цифр ИНН (для физических лиц)
                </small>
              </div>
            </div>

            <div class="form-group">
              <label for="consumer_passport_issued_by">Кем выдан паспорт</label>
              <input 
                id="consumer_passport_issued_by"
                v-model="consumerForm.passport_issued_by" 
                type="text" 
                class="form-control"
                :class="{ 'is-invalid': consumerErrors.passport_issued_by }"
                placeholder="Например: ОТДЕЛЕНИЕМ УФМС РОССИИ"
                maxlength="70"
              />
              <div v-if="consumerErrors.passport_issued_by" class="invalid-feedback">
                {{ consumerErrors.passport_issued_by[0] }}
              </div>
            </div>

            <div class="form-group">
              <label for="consumer_passport_issued_date">Дата выдачи паспорта</label>
              <input 
                id="consumer_passport_issued_date"
                v-model="consumerForm.passport_issued_date" 
                type="date" 
                class="form-control"
                :class="{ 'is-invalid': consumerErrors.passport_issued_date }"
                :max="today"
              />
              <div v-if="consumerErrors.passport_issued_date" class="invalid-feedback">
                {{ consumerErrors.passport_issued_date[0] }}
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeConsumerModal" class="btn btn-secondary">
                Отмена
              </button>
              <button type="submit" :disabled="consumerLoading" class="btn btn-primary">
                <i v-if="consumerLoading" class="fas fa-spinner fa-spin"></i>
                Создать
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ProductAPI, SellerAPI, ConsumerAPI } from '../services/api.js'

export default {
  name: 'ProductForm',
  props: {
    product: {
      type: Object,
      default: null
    },
    sellers: {
      type: Array,
      default: () => []
    },
    consumers: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      form: {
        title: '',
        model: '',
        serial_number: '',
        price: null,
        date_of_buying: '',
        warranty_period: null,
        seller_id: '',
        consumer_id: ''
      },
      errors: {},
      loading: false,
      showSellerModal: false,
      sellerForm: {
        title: '',
        address: '',
        ogrn: ''
      },
      sellerErrors: {},
      sellerLoading: false,
      showConsumerModal: false,
      consumerForm: {
        first_name: '',
        last_name: '',
        middle_name: '',
        address: '',
        passport: '',
        passport_issued_by: '',
        passport_issued_date: '',
        inn: ''
      },
      consumerErrors: {},
      consumerLoading: false
    }
  },
  computed: {
    isEdit() {
      return !!this.product
    },
    today() {
      return new Date().toISOString().split('T')[0]
    }
  },
  mounted() {
    if (this.product) {
      this.fillForm()
    }
  },
  methods: {
    fillForm() {
      this.form = {
        title: this.product.title || '',
        model: this.product.model || '',
        serial_number: this.product.serial_number || '',
        price: this.product.price || null,
        date_of_buying: this.product.date_of_buying || '',
        warranty_period: this.product.warranty_period || null,
        seller_id: this.product.seller_id || '',
        consumer_id: this.product.consumer_id || ''
      }
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        let response
        
        if (this.isEdit) {
          response = await ProductAPI.updateProduct(this.product.id, this.form)
        } else {
          response = await ProductAPI.createProduct(this.form)
        }
        
        if (response.success) {
          console.log(
            this.isEdit ? 'Товар успешно обновлен' : 'Товар успешно создан'
          )
          this.$emit('saved')
        } else {
          if (response.errors) {
            this.errors = response.errors
          } else {
            this.$toast.error(response.message || 'Произошла ошибка')
          }
        }
      } catch (error) {
        console.error('Error submitting form:', error)
        this.$toast.error('Произошла ошибка при отправке формы')
      } finally {
        this.loading = false
      }
    },
    
    closeModal() {
      this.$emit('close')
    },
    
    openSellerModal() {
      this.showSellerModal = true
      this.sellerForm = {
        title: '',
        address: '',
        ogrn: ''
      }
      this.sellerErrors = {}
    },
    
    closeSellerModal() {
      this.showSellerModal = false
      this.sellerForm = {
        title: '',
        address: '',
        ogrn: ''
      }
      this.sellerErrors = {}
    },
    
    async submitSellerForm() {
      this.sellerLoading = true
      this.sellerErrors = {}
      
      try {
        const response = await SellerAPI.createSeller(this.sellerForm)
        
        if (response.success) {
          console.log('Продавец успешно создан')
          this.closeSellerModal()
          // Обновляем список продавцов
          this.$emit('seller-created', response.data)
          // Автоматически выбираем созданного продавца
          this.form.seller_id = response.data.id
        } else {
          if (response.errors) {
            this.sellerErrors = response.errors
          } else {
            this.$toast.error(response.message || 'Произошла ошибка')
          }
        }
      } catch (error) {
        console.error('Error creating seller:', error)
        this.$toast.error('Произошла ошибка при создании продавца')
      } finally {
        this.sellerLoading = false
      }
    },
    
    // Методы для работы с потребителями
    openConsumerModal() {
      this.showConsumerModal = true
      this.consumerForm = {
        first_name: '',
        last_name: '',
        middle_name: '',
        address: '',
        passport: '',
        passport_issued_by: '',
        passport_issued_date: '',
        inn: ''
      }
      this.consumerErrors = {}
    },
    
    closeConsumerModal() {
      this.showConsumerModal = false
      this.consumerForm = {
        first_name: '',
        last_name: '',
        middle_name: '',
        address: '',
        passport: '',
        passport_issued_by: '',
        passport_issued_date: '',
        inn: ''
      }
      this.consumerErrors = {}
    },
    
    async submitConsumerForm() {
      this.consumerLoading = true
      this.consumerErrors = {}
      
      try {
        const response = await ConsumerAPI.createConsumer(this.consumerForm)
        
        if (response.success) {
          console.log('Потребитель успешно создан')
          this.closeConsumerModal()
          // Обновляем список потребителей
          this.$emit('consumer-created', response.data)
          // Автоматически выбираем созданного потребителя
          this.form.consumer_id = response.data.id
        } else {
          if (response.errors) {
            this.consumerErrors = response.errors
          } else {
            this.$toast.error(response.message || 'Произошла ошибка')
          }
        }
      } catch (error) {
        console.error('Error creating consumer:', error)
        this.$toast.error('Произошла ошибка при создании потребителя')
      } finally {
        this.consumerLoading = false
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

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #555;
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
  color: #dc3545;
  font-size: 12px;
  margin-top: 5px;
}

.seller-input-group {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.consumer-input-group {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.seller-input-group .form-control {
  flex: 1;
}

.consumer-input-group .form-control {
  flex: 1;
}

.btn-outline-primary {
  background: transparent;
  border: 1px solid #007bff;
  color: #007bff;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  white-space: nowrap;
}

.btn-outline-primary:hover {
  background: #007bff;
  color: white;
}

.btn-sm {
  padding: 6px 10px;
  font-size: 12px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
