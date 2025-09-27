<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal large-modal" @click.stop>
      <div class="modal-header">
        <h3>Информация о продавце</h3>
        <button @click="closeModal" class="close-btn">
          ✕
        </button>
      </div>
      
      <div class="modal-body">
        <div class="seller-info">
          <div class="form-group">
            <label for="title">Название</label>
            <input 
              id="title"
              :value="seller?.title || 'Не указано'" 
              type="text" 
              class="form-control"
              readonly
            />
          </div>

          <div class="form-group">
            <label for="address">Адрес</label>
            <textarea 
              id="address"
              :value="seller?.address || 'Не указано'" 
              class="form-control"
              readonly
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="ogrn">ОГРН</label>
            <input 
              id="ogrn"
              :value="seller?.ogrn || 'Не указано'" 
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
                :value="formatDate(seller?.created_at)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group">
              <label for="updated_at">Дата обновления</label>
              <input 
                id="updated_at"
                :value="formatDate(seller?.updated_at)" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="editSeller" class="btn btn-warning">
          ✏️ Редактировать
        </button>
        <button @click="closeModal" class="btn btn-secondary">
          Закрыть
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SellerViewForm',
  props: {
    seller: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit'],
  methods: {
    formatDate(date) {
      if (!date) return 'Не указано'
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    editSeller() {
      this.$emit('edit', this.seller)
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
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.large-modal {
  max-width: 800px;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
}

.modal-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
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
  border-radius: 50%;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background-color: #e9ecef;
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.seller-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.form-control {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background-color: #f8f9fa;
  color: #666;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #eee;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  background: #f8f9fa;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

@media (max-width: 768px) {
  .modal {
    width: 95%;
    margin: 10px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
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
