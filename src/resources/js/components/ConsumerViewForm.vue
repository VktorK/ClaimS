<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>Информация о потребителе</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>
      
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label for="last_name">Фамилия</label>
            <input 
              id="last_name"
              :value="consumer.last_name" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group">
            <label for="first_name">Имя</label>
            <input 
              id="first_name"
              :value="consumer.first_name" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group">
            <label for="middle_name">Отчество</label>
            <input 
              id="middle_name"
              :value="consumer.middle_name || '-'" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>
        </div>

        <div class="form-group">
          <label for="address">Адрес</label>
          <textarea 
            id="address"
            :value="consumer.address" 
            class="form-control"
            readonly
            rows="3"
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="passport">Паспорт</label>
            <input 
              id="passport"
              :value="consumer.formatted_passport" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group">
            <label for="inn">ИНН</label>
            <input 
              id="inn"
              :value="consumer.formatted_inn || '-'" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>
        </div>

        <div class="form-group" v-if="consumer.passport_issued_by">
          <label for="passport_issued_by">Кем выдан паспорт</label>
          <input 
            id="passport_issued_by"
            :value="consumer.passport_issued_by" 
            type="text" 
            class="form-control"
            readonly
          />
        </div>

        <div class="form-group" v-if="consumer.passport_issued_date">
          <label for="passport_issued_date">Дата выдачи паспорта</label>
          <input 
            id="passport_issued_date"
            :value="formatDate(consumer.passport_issued_date)" 
            type="text" 
            class="form-control"
            readonly
          />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="created_at">Дата создания</label>
            <input 
              id="created_at"
              :value="formatDate(consumer.created_at)" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group">
            <label for="updated_at">Дата обновления</label>
            <input 
              id="updated_at"
              :value="formatDate(consumer.updated_at)" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="editConsumer" class="btn btn-warning">
          ✏️ Редактировать
        </button>
        <button type="button" @click="deleteConsumer" class="btn btn-danger">
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
  name: 'ConsumerViewForm',
  props: {
    consumer: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit', 'delete'],
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
    
    editConsumer() {
      this.$emit('edit', this.consumer)
    },
    
    deleteConsumer() {
      this.$emit('delete', this.consumer)
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
  background-color: #f8f9fa;
  color: #495057;
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
