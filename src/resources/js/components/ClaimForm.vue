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

          <!-- Выбор шаблона претензии -->
          <div class="form-group">
            <label for="template_id">Шаблон претензии</label>
            <div class="template-selector">
              <select 
                id="template_id"
                v-model="selectedTemplateId" 
                class="form-control"
                @change="onTemplateChange"
              >
                <option value="">Выберите шаблон (необязательно)</option>
                <option v-for="template in templates" :key="template.id" :value="template.id">
                  {{ template.name }}
                </option>
              </select>
              <button type="button" @click="openTemplateManager" class="btn btn-outline-primary btn-sm">
                📝 Управление шаблонами
              </button>
            </div>
            <small class="form-text text-muted">
              Выберите шаблон для автоматического заполнения полей претензии
            </small>
          </div>

          <!-- Предпросмотр шаблона -->
          <div v-if="renderedTemplate" class="form-group">
            <label>Предпросмотр претензии:</label>
            <div class="template-preview">
              <div class="preview-content" v-html="renderedTemplate"></div>
            </div>
          </div>

          <!-- Секция о ремонте -->
          <div class="form-section">
            <h4>Информация о ремонте</h4>
            
            <div class="form-group">
              <label>Был ли товар ранее в ремонте? *</label>
              <div class="radio-group">
                <label class="radio-option">
                  <input 
                    type="radio" 
                    v-model="form.was_in_repair" 
                    :value="true"
                  />
                  <span>Да</span>
                </label>
                <label class="radio-option">
                  <input 
                    type="radio" 
                    v-model="form.was_in_repair" 
                    :value="false"
                  />
                  <span>Нет</span>
                </label>
              </div>
            </div>

            <div v-if="form.was_in_repair === true" class="conditional-fields">
              <div class="form-group">
                <label for="service_center_documents">Реквизиты документа из сервисного центра</label>
                <textarea 
                  id="service_center_documents"
                  v-model="form.service_center_documents" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.service_center_documents }"
                  placeholder="Введите реквизиты документа из сервисного центра"
                  rows="3"
                ></textarea>
                <div v-if="errors.service_center_documents" class="invalid-feedback">
                  {{ errors.service_center_documents[0] }}
                </div>
              </div>
              
              <div class="form-group">
                <label for="previous_defect">С каким недостатком был ранее</label>
                <textarea 
                  id="previous_defect"
                  v-model="form.previous_defect" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.previous_defect }"
                  placeholder="Укажите с каким недостатком был ранее"
                  rows="3"
                ></textarea>
                <div v-if="errors.previous_defect" class="invalid-feedback">
                  {{ errors.previous_defect[0] }}
                </div>
              </div>
            </div>

            <div v-if="form.was_in_repair === false" class="conditional-fields">
              <div class="form-group">
                <label for="current_defect">Какой недостаток был обнаружен</label>
                <textarea 
                  id="current_defect"
                  v-model="form.current_defect" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.current_defect }"
                  placeholder="Укажите какой недостаток был обнаружен"
                  rows="3"
                ></textarea>
                <div v-if="errors.current_defect" class="invalid-feedback">
                  {{ errors.current_defect[0] }}
                </div>
              </div>
            </div>
          </div>

          <!-- Секция об экспертизе -->
          <div class="form-section">
            <h4>Информация об экспертизе</h4>
            
            <div class="form-group">
              <label>Проводилась ли экспертиза или проверка качества? *</label>
              <div class="radio-group">
                <label class="radio-option">
                  <input 
                    type="radio" 
                    v-model="form.expertiseConducted" 
                    :value="true"
                  />
                  <span>Экспертиза (Проверка качества)</span>
                </label>
                <label class="radio-option">
                  <input 
                    type="radio" 
                    v-model="form.expertiseConducted" 
                    :value="false"
                    :disabled="!canSelectNoExpertise"
                  />
                  <span>Экспертиза (Проверка качества) не проводилась</span>
                </label>
              </div>
              <div v-if="!canSelectNoExpertise && form.expertiseConducted === false" class="warning-message">
                ⚠️ Этот вариант доступен только если гарантийный срок товара ещё не закончился или не был указан
              </div>
            </div>

            <div v-if="form.expertiseConducted === true" class="conditional-fields">
              <div class="form-group">
                <label for="expertiseData">Данные о проведенной экспертизе</label>
                <textarea 
                  id="expertiseData"
                  v-model="form.expertiseData" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.expertiseData }"
                  placeholder="Укажите данные о проведенной экспертизе"
                  rows="3"
                ></textarea>
                <div v-if="errors.expertiseData" class="invalid-feedback">
                  {{ errors.expertiseData[0] }}
                </div>
              </div>
              
              <div class="form-group">
                <label for="expertiseDefect">Недостаток согласно экспертизе</label>
                <textarea 
                  id="expertiseDefect"
                  v-model="form.expertiseDefect" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.expertiseDefect }"
                  placeholder="Опишите недостаток согласно экспертизе"
                  rows="3"
                ></textarea>
                <div v-if="errors.expertiseDefect" class="invalid-feedback">
                  {{ errors.expertiseDefect[0] }}
                </div>
              </div>
            </div>

            <div v-if="form.expertiseConducted === false" class="conditional-fields">
              <div class="form-group">
                <label for="actualDefect">Укажите настоящий недостаток</label>
                <textarea 
                  id="actualDefect"
                  v-model="form.actualDefect" 
                  class="form-control"
                  :class="{ 'is-invalid': errors.actualDefect }"
                  placeholder="Опишите настоящий недостаток товара"
                  rows="3"
                ></textarea>
                <div v-if="errors.actualDefect" class="invalid-feedback">
                  {{ errors.actualDefect[0] }}
                </div>
              </div>
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
    
    <!-- Модальное окно управления шаблонами -->
    <ClaimTemplateManager 
      v-if="showTemplateManager" 
      @close="onTemplateManagerClose"
    />
  </div>
</template>

<script>
import { ClaimAPI, ProductAPI, ClaimTemplateAPI } from '../services/api.js'
import ClaimTemplateManager from './ClaimTemplateManager.vue'

export default {
  name: 'ClaimForm',
  components: {
    ClaimTemplateManager
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
        template_id: '',
        type: '',
        status: 'pending',
        was_in_repair: null,
        service_center_documents: '',
        previous_defect: '',
        current_defect: '',
        expertiseConducted: null,
        expertiseData: '',
        expertiseDefect: '',
        actualDefect: '',
        claimed_amount: '',
        claim_date: '',
        resolution_date: '',
        resolution_notes: ''
      },
      errors: {},
      loading: false,
      templates: [],
      selectedTemplateId: '',
      renderedTemplate: '',
      showTemplateManager: false
    }
  },
  computed: {
    isEdit() {
      return !!this.claim
    },
    
    canSelectNoExpertise() {
      if (!this.form.product_id) return true
      
      const product = this.products.find(p => p.id === this.form.product_id)
      if (!product) return true
      
      // Если гарантийный срок не указан, можно выбрать "не проводилась"
      if (!product.warranty_period) return true
      
      // Проверяем, не истёк ли гарантийный срок
      if (!product.date_of_buying) return true
      
      const purchaseDate = new Date(product.date_of_buying)
      const warrantyEndDate = new Date(purchaseDate)
      warrantyEndDate.setMonth(warrantyEndDate.getMonth() + product.warranty_period)
      
      return warrantyEndDate >= new Date()
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
  async mounted() {
    await this.loadTemplates()
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
        template_id: this.claim.template_id || '',
        type: this.claim.type || '',
        status: this.claim.status || 'pending',
        was_in_repair: this.claim.was_in_repair ?? null,
        service_center_documents: this.claim.service_center_documents || '',
        previous_defect: this.claim.previous_defect || '',
        current_defect: this.claim.current_defect || '',
        expertiseConducted: this.claim.expertiseConducted ?? null,
        expertiseData: this.claim.expertiseData || '',
        expertiseDefect: this.claim.expertiseDefect || '',
        actualDefect: this.claim.actualDefect || '',
        claimed_amount: this.claim.claimed_amount || '',
        claim_date: this.claim.claim_date || '',
        resolution_date: this.claim.resolution_date || '',
        resolution_notes: this.claim.resolution_notes || ''
      }
      // Если есть template_id, установим его и в selectedTemplateId
      if (this.claim.template_id) {
        this.selectedTemplateId = this.claim.template_id
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
    
    async loadTemplates() {
      try {
        const response = await ClaimTemplateAPI.getTemplates('', true)
        if (response.success) {
          this.templates = response.data
        }
      } catch (error) {
        console.error('Ошибка загрузки шаблонов:', error)
      }
    },
    
    async onTemplateChange() {
      if (!this.selectedTemplateId) {
        this.renderedTemplate = ''
        this.form.template_id = ''
        return
      }
      
      // Сохраняем выбранный template_id в форму
      this.form.template_id = this.selectedTemplateId
      
      try {
        const templateData = this.prepareTemplateData()
        const response = await ClaimTemplateAPI.renderTemplate(this.selectedTemplateId, templateData)
        if (response.success) {
          this.renderedTemplate = response.data.rendered_content
        }
      } catch (error) {
        console.error('Ошибка рендеринга шаблона:', error)
      }
    },
    
    prepareTemplateData() {
      // Проверяем что products загружены и есть выбранный товар
      if (!this.products || !Array.isArray(this.products) || !this.form.product_id) {
        return {}
      }
      
      const selectedProduct = this.products.find(p => p.id === this.form.product_id)
      if (!selectedProduct) return {}
      
      // Создаем безопасные объекты с значениями по умолчанию
      const consumer = selectedProduct.consumer || {}
      const seller = selectedProduct.seller || {}
      
      return {
        // Данные претензии (для валидации)
        product_id: this.form.product_id,
        type: this.form.type || 'defect',
        was_in_repair: this.form.was_in_repair || false,
        service_center_documents: this.form.service_center_documents || '',
        previous_defect: this.form.previous_defect || '',
        current_defect: this.form.current_defect || '',
        expertiseConducted: this.form.expertiseConducted || false,
        expertiseData: this.form.expertiseData || '',
        expertiseDefect: this.form.expertiseDefect || '',
        actualDefect: this.form.actualDefect || '',
        claimed_amount: this.form.claimed_amount || null,
        claim_date: this.form.claim_date || new Date().toISOString().split('T')[0],
        
        // Данные для подстановки в шаблон (плоская структура)
        'consumer.full_name': consumer.full_name || '',
        'consumer.short_name': consumer.short_name || '',
        'consumer.address': consumer.address || '',
        'consumer.passport': consumer.passport || '',
        'consumer.inn': consumer.inn || '',
        'consumer.passport_issued_by': consumer.passport_issued_by || '',
        'consumer.passport_issue_date': consumer.passport_issue_date || '',
        
        'seller.title': seller.title || '',
        'seller.short_title': seller.short_title || '',
        'seller.address': seller.address || '',
        'seller.inn': seller.inn || '',
        'seller.ogrn': seller.ogrn || '',
        'seller.phone': seller.phone || '',
        'seller.email': seller.email || '',
        
        'product.title': selectedProduct.title || '',
        'product.model': selectedProduct.model || '',
        'product.serial_number': selectedProduct.serial_number || '',
        'product.price': selectedProduct.price || '',
        'product.date_of_buying': selectedProduct.date_of_buying || '',
        'product.warranty_period': selectedProduct.warranty_period || '',
        
        'claim.type': this.form.type || '',
        'claim.current_defect': this.form.current_defect || '',
        'claim.previous_repair': this.form.was_in_repair ? 'Да' : 'Нет',
        'claim.repair_document': this.form.service_center_documents || '',
        'claim.previous_defect': this.form.previous_defect || '',
        'claim.expertise_conducted': this.form.expertiseConducted ? 'Да' : 'Нет',
        'claim.expertise_data': this.form.expertiseData || '',
        'claim.expertise_defect': this.form.expertiseDefect || '',
        'claim.real_defect': this.form.actualDefect || '',
        'claim.claimed_amount': this.form.claimed_amount || '',
        'claim.claim_date': this.form.claim_date || '',
        
        'current_date': new Date().toLocaleDateString('ru-RU'),
        'current_time': new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }),
        'user.name': this.user?.name || '',
        'user.email': this.user?.email || ''
      }
    },
    
    openTemplateManager() {
      this.showTemplateManager = true
    },
    
    onTemplateManagerClose() {
      this.showTemplateManager = false
      this.loadTemplates()
    },
    
    onProductChange() {
      // При изменении товара больше не показываем модальные окна
      // Все поля теперь встроены в форму
      // Обновляем предпросмотр шаблона если он выбран
      if (this.selectedTemplateId) {
        this.onTemplateChange()
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
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-section {
  margin: 20px 0;
  padding: 20px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background-color: #f8f9fa;
}

.form-section h4 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
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
  background-color: #e9ecef;
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

.conditional-fields {
  margin-top: 15px;
  padding: 15px;
  background-color: white;
  border: 1px solid #dee2e6;
  border-radius: 4px;
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

.form-text {
  display: block;
  margin-top: 5px;
  font-size: 12px;
  color: #6c757d;
}

.template-selector {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.template-selector .form-control {
  flex: 1;
}

.template-preview {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 15px;
  background: #f8f9fa;
  max-height: 300px;
  overflow-y: auto;
}

.preview-content {
  font-size: 14px;
  line-height: 1.5;
  color: #333;
}

.btn-outline-primary {
  background: transparent;
  border: 1px solid #007bff;
  color: #007bff;
}

.btn-outline-primary:hover {
  background: #007bff;
  color: white;
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
