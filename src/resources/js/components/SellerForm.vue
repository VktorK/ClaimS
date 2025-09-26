<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Редактировать продавца' : 'Добавить продавца' }}</h3>
        <button @click="closeModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <!-- Поиск организации по ИНН -->
          <div class="form-group">
            <label for="inn_search">Поиск организации по ИНН</label>
            <div class="inn-search-container">
              <input 
                id="inn_search"
                v-model="innSearch" 
                type="text" 
                class="form-control"
                placeholder="Введите ИНН для поиска организации"
                @input="searchOrganization"
              />
              <button 
                type="button" 
                @click="searchOrganization" 
                class="btn btn-info btn-sm search-btn"
                :disabled="!innSearch || innSearch.length < 10"
              >
                🔍 Поиск
              </button>
            </div>
            <div v-if="searchLoading" class="search-loading">
              ⏳ Поиск организации...
            </div>
            <div v-if="searchError" class="search-error">
              ❌ {{ searchError }}
            </div>
            <div v-if="foundOrganization" class="found-organization">
              <div class="org-info">
                <h4>Найденная организация:</h4>
                <p><strong>Название:</strong> {{ foundOrganization.name }}</p>
                <p v-if="foundOrganization.address"><strong>Адрес:</strong> {{ foundOrganization.address }}</p>
                <p v-if="foundOrganization.inn"><strong>ИНН:</strong> {{ foundOrganization.inn }}</p>
                <p v-if="foundOrganization.ogrn"><strong>ОГРН:</strong> {{ foundOrganization.ogrn }}</p>
              </div>
              <button 
                type="button" 
                @click="useFoundOrganization" 
                class="btn btn-success btn-sm"
              >
                ✅ Использовать данные
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="title">Название *</label>
            <input 
              id="title"
              v-model="form.title" 
              type="text" 
              class="form-control"
              :class="{ 'is-invalid': errors.title }"
              placeholder="Введите название продавца"
              required
            />
            <div v-if="errors.title" class="invalid-feedback">
              {{ errors.title[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="address">Адрес</label>
            <textarea 
              id="address"
              v-model="form.address" 
              class="form-control"
              :class="{ 'is-invalid': errors.address }"
              placeholder="Введите адрес продавца"
              rows="3"
            ></textarea>
            <div v-if="errors.address" class="invalid-feedback">
              {{ errors.address[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="ogrn">ОГРН</label>
            <input 
              id="ogrn"
              v-model="form.ogrn" 
              type="text" 
              class="form-control"
              :class="{ 'is-invalid': errors.ogrn }"
              placeholder="Основной государственный регистрационный номер"
              maxlength="15"
              pattern="[0-9]{13,15}"
            />
            <div v-if="errors.ogrn" class="invalid-feedback">
              {{ errors.ogrn[0] }}
            </div>
            <small class="form-text">
              ОГРН состоит из 13-15 цифр (необязательное поле)
            </small>
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
import { SellerAPI } from '../services/api.js'
import { DaDataAPI } from '../services/dadata.js'

export default {
  name: 'SellerForm',
  props: {
    seller: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      form: {
        title: '',
        address: '',
        ogrn: ''
      },
      errors: {},
      loading: false,
      innSearch: '',
      searchLoading: false,
      searchError: '',
      foundOrganization: null
    }
  },
  computed: {
    isEdit() {
      return !!this.seller
    }
  },
  mounted() {
    if (this.seller) {
      this.fillForm()
    }
  },
  methods: {
    fillForm() {
      this.form = {
        title: this.seller.title || '',
        address: this.seller.address || '',
        ogrn: this.seller.ogrn || ''
      }
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        let response
        
        if (this.isEdit) {
          response = await SellerAPI.updateSeller(this.seller.id, this.form)
        } else {
          response = await SellerAPI.createSeller(this.form)
        }
        
        if (response.success) {
          console.log(
            this.isEdit ? 'Продавец успешно обновлен' : 'Продавец успешно создан'
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
    
    async searchOrganization() {
      if (!this.innSearch || this.innSearch.length < 10) {
        return
      }
      
      this.searchLoading = true
      this.searchError = ''
      this.foundOrganization = null
      
      try {
        const response = await DaDataAPI.findOrganizationByInn(this.innSearch)
        const organization = DaDataAPI.processOrganizationData(response)
        
        if (organization) {
          this.foundOrganization = organization
        } else {
          this.searchError = 'Организация не найдена'
        }
      } catch (error) {
        console.error('Ошибка поиска организации:', error)
        this.searchError = 'Ошибка при поиске организации. Проверьте API ключ DaData.'
      } finally {
        this.searchLoading = false
      }
    },
    
    useFoundOrganization() {
      if (this.foundOrganization) {
        this.form.title = this.foundOrganization.name || ''
        this.form.address = this.foundOrganization.address || ''
        this.form.ogrn = this.foundOrganization.ogrn || ''
        
        // Очищаем результаты поиска
        this.foundOrganization = null
        this.innSearch = ''
        this.searchError = ''
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
  max-width: 500px;
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
  font-family: inherit;
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

.form-text {
  color: #6c757d;
  font-size: 12px;
  margin-top: 5px;
  display: block;
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
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
}

/* Стили для поиска организации */
.inn-search-container {
  display: flex;
  gap: 10px;
  align-items: center;
}

.inn-search-container .form-control {
  flex: 1;
}

.search-btn {
  white-space: nowrap;
}

.search-loading {
  color: #007bff;
  font-size: 14px;
  margin-top: 8px;
}

.search-error {
  color: #dc3545;
  font-size: 14px;
  margin-top: 8px;
}

.found-organization {
  margin-top: 15px;
  padding: 15px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
}

.org-info h4 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 16px;
}

.org-info p {
  margin: 5px 0;
  color: #666;
  font-size: 14px;
}

.org-info strong {
  color: #333;
}
</style>
