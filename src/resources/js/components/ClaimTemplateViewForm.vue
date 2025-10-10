<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>Просмотр шаблона</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>
      
      <div class="modal-body">
        <div class="template-info">
          <div class="form-group">
            <label for="name">Название шаблона</label>
            <input 
              id="name"
              :value="template?.name || 'Не указано'" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group" v-if="template?.description">
            <label for="description">Описание</label>
            <textarea 
              id="description"
              :value="template?.description || 'Не указано'" 
              class="form-control"
              readonly
              rows="2"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Статус</label>
            <div class="status-display">
              <span :class="['status-badge', template?.is_active ? 'active' : 'inactive']">
                {{ template?.is_active ? 'Активен' : 'Неактивен' }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label for="template_content">Содержимое шаблона</label>
            <div class="template-content-display">
              <pre class="template-code">{{ template?.template_content || 'Не указано' }}</pre>
            </div>
          </div>

          <!-- Предпросмотр с примерами -->
          <div class="form-group">
            <label>Предпросмотр с примерами данных:</label>
            <div class="preview-panel">
              <div class="preview-content" v-html="previewWithExamples"></div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="created_at">Дата создания</label>
              <input 
                id="created_at"
                :value="formatDate(template?.created_at)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group">
              <label for="updated_at">Дата обновления</label>
              <input 
                id="updated_at"
                :value="formatDate(template?.updated_at)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="downloadTemplate" class="btn btn-success">
          📥 Скачать
        </button>
        <button type="button" @click="editTemplate" class="btn btn-warning">
          ✏️ Редактировать
        </button>
        <button type="button" @click="deleteTemplate" class="btn btn-danger">
          🗑️ Удалить
        </button>
        <button type="button" @click="closeModal" class="btn btn-secondary">
          Закрыть
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClaimTemplateViewForm',
  props: {
    template: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit', 'delete'],
  computed: {
    previewWithExamples() {
      if (!this.template?.template_content) return ''
      
      // Заменяем плейсхолдеры на примеры для предпросмотра
      let content = this.template.template_content
      
      // Примеры данных для предпросмотра
      const examples = {
        'consumer.full_name': 'Иванов Иван Иванович',
        'consumer.short_name': 'Иванов И.И.',
        'consumer.address': 'г. Москва, ул. Примерная, д. 1, кв. 10',
        'consumer.passport': '1234567890',
        'consumer.formatted_passport': '1234 567890',
        'consumer.inn': '123456789012',
        'consumer.formatted_inn': '1234 5678 9012',
        'consumer.passport_issued_by': 'ОТДЕЛЕНИЕМ УФМС РОССИИ ПО Г. МОСКВЕ',
        'consumer.passport_issued_date': '15.01.2010',
        
        'product.title': 'Смартфон Samsung Galaxy',
        'product.model': 'Galaxy S21',
        'product.serial_number': 'SN123456789',
        'product.price': '50 000',
        'product.date_of_buying': '15.01.2024',
        'product.warranty_period': '12',
        
        'seller.title': 'ООО "Электроника"',
        'seller.address': 'г. Москва, ул. Торговая, д. 10',
        'seller.ogrn': '1234567890123',
        
        'claim.type': 'По качеству товара',
        'claim.status': 'Ожидает рассмотрения',
        'claim.created_at': '20.01.2024',
        'claim.was_in_repair': 'Да',
        'claim.service_center_documents': 'Акт о ремонте №123 от 10.01.2024',
        'claim.previous_defect': 'Не включается экран',
        'claim.current_defect': 'Не работает камера',
        'claim.expertiseConducted': 'Да',
        'claim.expertiseData': 'Заключение эксперта №456 от 18.01.2024',
        'claim.expertiseDefect': 'Производственный брак матрицы камеры',
        'claim.actualDefect': 'Не работает основная камера'
      }
      
      // Заменяем все плейсхолдеры
      Object.entries(examples).forEach(([placeholder, value]) => {
        const regex = new RegExp(`{{${placeholder}}}`, 'g')
        content = content.replace(regex, `<span class="placeholder-value">${value}</span>`)
      })
      
      // Заменяем переносы строк на <br>
      content = content.replace(/\n/g, '<br>')
      
      return content
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    editTemplate() {
      this.$emit('edit', this.template)
    },
    
    deleteTemplate() {
      this.$emit('delete', this.template)
    },
    
    async downloadTemplate() {
      if (!this.template?.id) {
        console.error('Нет ID шаблона для скачивания')
        return
      }
      
      try {
        console.log('Скачиваем шаблон через бэкенд...')
        
        // Получаем токен из localStorage
        const token = localStorage.getItem('token')
        if (!token) {
          console.error('Токен не найден')
          return
        }
        
        // Создаем ссылку для скачивания
        const downloadUrl = `/api/claim-templates/${this.template.id}/download`
        
        // Добавляем заголовок авторизации через fetch
        const response = await fetch(downloadUrl, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
          }
        })
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        // Получаем blob из ответа
        const blob = await response.blob()
        console.log('Blob получен, размер:', blob.size, 'тип:', blob.type)
        
        // Создаем URL для blob
        const blobUrl = URL.createObjectURL(blob)
        
        // Создаем ссылку для скачивания
        const fileName = `${this.template.name || 'template'}.docx`
        const link = document.createElement('a')
        link.href = blobUrl
        link.download = fileName
        
        // Добавляем ссылку в DOM, кликаем и удаляем
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        // Освобождаем память
        URL.revokeObjectURL(blobUrl)
        
        console.log(`✅ Шаблон "${this.template.name}" успешно скачан как .docx файл`)
        
      } catch (error) {
        console.error('❌ Ошибка при скачивании шаблона:', error)
        console.error('Детали ошибки:', error.message)
        
        // Fallback: показываем сообщение об ошибке
        alert('Ошибка при скачивании файла. Попробуйте еще раз.')
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
  max-width: 900px;
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
  background-color: #f8f9fa;
  color: #495057;
}

.status-display {
  padding: 10px 0;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.template-content-display {
  border: 1px solid #ddd;
  border-radius: 4px;
  background: #f8f9fa;
  max-height: 300px;
  overflow-y: auto;
}

.template-code {
  margin: 0;
  padding: 15px;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.4;
  color: #333;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.preview-panel {
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

.placeholder-value {
  background: #e3f2fd;
  color: #1976d2;
  padding: 2px 4px;
  border-radius: 3px;
  font-weight: 500;
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

.btn-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>
