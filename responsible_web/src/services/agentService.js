import api from './axios'

export const agentService = {
  list:         (params) => api.get('/agents', { params }),
  show:         (id)     => api.get(`/agents/${id}`),
  create:       (data)   => api.post('/agents', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update:       (id, data) => api.post(`/agents/${id}`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  toggleStatus: (id)     => api.patch(`/agents/${id}/toggle-status`),
  destroy:      (id)     => api.delete(`/agents/${id}`),
}
