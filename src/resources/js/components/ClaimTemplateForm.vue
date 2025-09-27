<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Редактировать шаблон' : 'Создать шаблон' }}</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>

      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="name">Название шаблона *</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': errors.name }"
              required
              placeholder="Например: Претензия по качеству товара"
            />
            <div v-if="errors.name" class="invalid-feedback">
              {{ errors.name[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="description">Описание</label>
            <textarea
              id="description"
              v-model="form.description"
              class="form-control"
              :class="{ 'is-invalid': errors.description }"
              placeholder="Краткое описание шаблона"
              rows="2"
            ></textarea>
            <div v-if="errors.description" class="invalid-feedback">
              {{ errors.description[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="template_content">Содержимое шаблона *</label>
            <div class="template-editor">
              <div class="editor-toolbar">
                <div class="toolbar-group">
                  <button type="button" @click="execCommand('bold')" class="btn btn-sm btn-outline-secondary" title="Жирный">
                    <strong>B</strong>
                  </button>
                  <button type="button" @click="execCommand('italic')" class="btn btn-sm btn-outline-secondary" title="Курсив">
                    <em>I</em>
                  </button>
                  <button type="button" @click="execCommand('underline')" class="btn btn-sm btn-outline-secondary" title="Подчеркнутый">
                    <u>U</u>
                  </button>
                </div>
                
                <div class="toolbar-group">
                  <select @change="changeFontSize" class="form-control form-control-sm font-size-select">
                    <option value="12">12px</option>
                    <option value="14" selected>14px</option>
                    <option value="16">16px</option>
                    <option value="18">18px</option>
                    <option value="20">20px</option>
                    <option value="24">24px</option>
                  </select>
                </div>
                
                <div class="toolbar-group">
                  <button type="button" @click="insertTab" class="btn btn-sm btn-outline-secondary" title="Отступ (Tab)">
                    ↹ Tab
                  </button>
                  <button type="button" @click="insertLineBreak" class="btn btn-sm btn-outline-secondary" title="Новая строка">
                    ↵ Enter
                  </button>
                  <button type="button" @click="insertSpaces" class="btn btn-sm btn-outline-info" title="4 пробела">
                    ␣␣␣␣
                  </button>
                </div>
                
                <div class="toolbar-group">
                  <button type="button" @click="insertPlaceholder" class="btn btn-sm btn-outline-primary">
                    📝 Вставить плейсхолдер
                  </button>
                  <button type="button" @click="showPlaceholders = !showPlaceholders" class="btn btn-sm btn-outline-secondary">
                    {{ showPlaceholders ? 'Скрыть' : 'Показать' }} плейсхолдеры
                  </button>
                </div>
              </div>

              <div v-if="showPlaceholders" class="placeholders-panel">
                <h5>Доступные плейсхолдеры:</h5>
                <div class="placeholders-grid">
                  <div v-for="(group, groupKey) in placeholders" :key="groupKey" class="placeholder-group">
                    <h6>{{ getGroupTitle(groupKey) }}</h6>
                    <div class="placeholder-items">
                      <button
                        v-for="(desc, key) in group"
                        :key="key"
                        type="button"
                        @click="insertPlaceholderText(`{{${groupKey}.${key}}}`)"
                        class="placeholder-item"
                        :title="desc"
                      >
                        {{ groupKey }}.{{ key }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div 
                ref="editor"
                class="rich-editor"
                :class="{ 'is-invalid': errors.template_content }"
                contenteditable="true"
                @paste="onPaste"
                @keydown="onKeyDown"
                @input="onEditorInput"
                placeholder="Введите содержимое шаблона..."
              ></div>
              
              <textarea 
                id="template_content"
                v-model="form.template_content" 
                class="hidden-textarea"
                required
              ></textarea>
            </div>
            <div v-if="errors.template_content" class="invalid-feedback">
              {{ errors.template_content[0] }}
            </div>
            <small class="form-text text-muted">
              Используйте плейсхолдеры вида &#123;&#123;consumer.full_name&#125;&#125; для автоматической подстановки данных
            </small>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="form-checkbox"
              />
              <span class="checkmark"></span>
              Активный шаблон
            </label>
            <small class="form-text text-muted">
              Только активные шаблоны доступны для выбора при создании претензий
            </small>
          </div>

          <!-- Предпросмотр -->
          <div v-if="form.template_content" class="form-group">
            <label>Предпросмотр:</label>
            <div class="preview-panel">
              <div class="preview-content" v-html="previewContent"></div>
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
import { ClaimTemplateAPI } from '../services/api.js'

export default {
  name: 'ClaimTemplateForm',
  props: {
    template: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      form: {
        name: '',
        description: '',
        template_content: '',
        is_active: true
      },
      errors: {},
      loading: false,
      showPlaceholders: false,
      placeholders: {}
    }
  },
  computed: {
    isEdit() {
      return !!this.template
    },

    previewContent() {
      if (!this.form.template_content) return ''

      // Простая замена плейсхолдеров для предпросмотра
      let content = this.form.template_content

      // Заменяем плейсхолдеры на примеры
      const examples = {
        'consumer.full_name': 'Иванов Иван Иванович',
        'consumer.address': 'г. Москва, ул. Примерная, д. 1',
        'consumer.formatted_passport': '1234 567890',
        'product.title': 'Смартфон Samsung Galaxy',
        'product.model': 'Galaxy S21',
        'product.serial_number': 'SN123456789',
        'product.price': '50 000',
        'product.date_of_buying': '15.01.2024',
        'seller.title': 'ООО "Электроника"',
        'seller.address': 'г. Москва, ул. Торговая, д. 10',
        'claim.type': 'По качеству товара',
        'claim.created_at': '20.01.2024'
      }

      Object.entries(examples).forEach(([placeholder, value]) => {
        content = content.replace(new RegExp(`{{${placeholder}}}`, 'g'), value)
      })

      return content.replace(/\n/g, '<br>')
    }
  },
  watch: {
    template: {
      handler(newTemplate) {
        if (newTemplate) {
          this.fillForm()
        }
      },
      immediate: true
    }
  },
  async mounted() {
    await this.loadPlaceholders()
    this.$nextTick(() => {
      this.initEditor()
    })
  },
  methods: {
    fillForm() {
      if (!this.template) return
      
      this.form = {
        name: this.template.name || '',
        description: this.template.description || '',
        template_content: this.template.template_content || '',
        is_active: this.template.is_active !== undefined ? this.template.is_active : true
      }
      
      // Инициализируем редактор
      this.$nextTick(() => {
        this.initEditor()
      })
    },
    
    initEditor() {
      if (this.$refs.editor) {
        this.$refs.editor.innerHTML = this.form.template_content || ''
        console.log('Editor initialized with content:', this.form.template_content)
        
        // Устанавливаем фокус на редактор
        this.$refs.editor.focus()
      }
    },

    async loadPlaceholders() {
      try {
        const response = await ClaimTemplateAPI.getPlaceholders()
        if (response.success) {
          this.placeholders = response.data
        }
      } catch (error) {
        console.error('Ошибка загрузки плейсхолдеров:', error)
      }
    },

    getGroupTitle(groupKey) {
      const titles = {
        'consumer': 'Потребитель',
        'product': 'Товар',
        'seller': 'Продавец',
        'claim': 'Претензия'
      }
      return titles[groupKey] || groupKey
    },

    insertPlaceholder() {
      const placeholder = prompt('Введите плейсхолдер (например: consumer.full_name):')
      if (placeholder) {
        this.insertPlaceholderText(`{{${placeholder}}}`)
      }
    },

    insertPlaceholderText(text) {
      this.insertTextAtCursor(text)
    },
    
    onKeyDown(event) {
      console.log('Vue keydown event:', event.key)
      if (event.key === 'Tab') {
        event.preventDefault()
        event.stopPropagation()
        console.log('Tab prevented, calling insertTab')
        this.insertTab()
        return false
      }
    },
    
    onEditorInput() {
      this.form.template_content = this.$refs.editor.innerHTML
    },
    
    
    onPaste(event) {
      event.preventDefault()
      const text = event.clipboardData.getData('text/plain')
      this.insertTextAtCursor(text)
    },
    
    insertTextAtCursor(text) {
      const editor = this.$refs.editor
      const selection = window.getSelection()
      
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0)
        range.deleteContents()
        
        // Создаем текстовый узел или HTML элемент в зависимости от содержимого
        let node
        if (text.includes('\n')) {
          // Если текст содержит переносы строк, создаем HTML
          const html = text.replace(/\n/g, '<br>')
          const tempDiv = document.createElement('div')
          tempDiv.innerHTML = html
          node = tempDiv.firstChild
        } else {
          node = document.createTextNode(text)
        }
        
        range.insertNode(node)
        
        // Устанавливаем курсор после вставленного текста
        range.setStartAfter(node)
        range.setEndAfter(node)
        selection.removeAllRanges()
        selection.addRange(range)
      } else {
        // Если нет выделения, добавляем в конец
        if (text.includes('\n')) {
          const html = text.replace(/\n/g, '<br>')
          editor.innerHTML += html
        } else {
          editor.innerHTML += text
        }
      }
      
      this.form.template_content = editor.innerHTML
    },
    
    execCommand(command) {
      document.execCommand(command, false, null)
      this.form.template_content = this.$refs.editor.innerHTML
    },
    
    changeFontSize(event) {
      const size = event.target.value
      document.execCommand('fontSize', false, '7')
      
      // Устанавливаем конкретный размер шрифта
      const selection = window.getSelection()
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0)
        const span = document.createElement('span')
        span.style.fontSize = size + 'px'
        
        try {
          range.surroundContents(span)
        } catch (e) {
          // Если не удается обернуть, вставляем span
          span.appendChild(range.extractContents())
          range.insertNode(span)
        }
        
        this.form.template_content = this.$refs.editor.innerHTML
      }
    },
    
    insertTab() {
      console.log('insertTab called')
      const editor = this.$refs.editor
      const selection = window.getSelection()
      
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0)
        const tabText = document.createTextNode('    ') // 4 пробела
        range.insertNode(tabText)
        
        // Устанавливаем курсор после вставленного текста
        range.setStartAfter(tabText)
        range.setEndAfter(tabText)
        selection.removeAllRanges()
        selection.addRange(range)
        
        console.log('Tab inserted successfully')
      } else {
        // Если нет выделения, добавляем в конец
        editor.innerHTML += '    '
        console.log('Tab added to end')
      }
      
      this.form.template_content = editor.innerHTML
    },
    
    insertSpaces() {
      console.log('insertSpaces called')
      this.insertTextAtCursor('    ') // 4 пробела
    },
    
    insertLineBreak() {
      this.insertTextAtCursor('<br>')
    },

    async submitForm() {
      this.loading = true
      this.errors = {}

      try {
        let response
        if (this.isEdit && this.template) {
          response = await ClaimTemplateAPI.updateTemplate(this.template.id, this.form)
        } else {
          response = await ClaimTemplateAPI.createTemplate(this.form)
        }

        if (response.success) {
          this.$emit('saved', response.data)
        } else {
          this.errors = response.errors || {}
        }
      } catch (error) {
        console.error('Ошибка при сохранении шаблона:', error)
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

.template-editor {
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.editor-toolbar {
  display: flex;
  gap: 10px;
  padding: 10px;
  background: #f8f9fa;
  border-bottom: 1px solid #ddd;
  flex-wrap: wrap;
  align-items: center;
}

.toolbar-group {
  display: flex;
  gap: 5px;
  align-items: center;
}

.font-size-select {
  width: auto;
  min-width: 70px;
}

.rich-editor {
  min-height: 300px;
  padding: 12px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  border: none;
  outline: none;
  background: white;
  overflow-y: auto;
}

.rich-editor:empty:before {
  content: attr(placeholder);
  color: #999;
  font-style: italic;
}

.rich-editor:focus {
  outline: none;
}

.hidden-textarea {
  display: none;
}

.template-textarea {
  border: none;
  border-radius: 0;
  resize: vertical;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.4;
}

.template-textarea:focus {
  box-shadow: none;
  border: none;
}

.placeholders-panel {
  padding: 15px;
  background: #f8f9fa;
  border-bottom: 1px solid #ddd;
  max-height: 200px;
  overflow-y: auto;
}

.placeholders-panel h5 {
  margin: 0 0 10px 0;
  font-size: 14px;
  color: #333;
}

.placeholders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.placeholder-group h6 {
  margin: 0 0 8px 0;
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
}

.placeholder-items {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.placeholder-item {
  padding: 4px 8px;
  background: #e9ecef;
  border: 1px solid #ced4da;
  border-radius: 3px;
  font-size: 11px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.placeholder-item:hover {
  background: #dee2e6;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-weight: normal;
}

.form-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.preview-panel {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 15px;
  background: #f8f9fa;
  max-height: 200px;
  overflow-y: auto;
}

.preview-content {
  font-size: 14px;
  line-height: 1.5;
  color: #333;
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

.btn-outline-primary {
  background: transparent;
  border: 1px solid #007bff;
  color: #007bff;
}

.btn-outline-primary:hover {
  background: #007bff;
  color: white;
}

.btn-outline-secondary {
  background: transparent;
  border: 1px solid #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background: #6c757d;
  color: white;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
}
</style>
