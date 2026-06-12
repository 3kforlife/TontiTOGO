import api from './axios'

export const tontineService = {
  list:             (params)   => api.get('/tontines', { params }),
  show:             (id)       => api.get(`/tontines/${id}`),
  create:           (data)     => api.post('/tontines', data),
  update:           (id, data) => api.put(`/tontines/${id}`, data),
  destroy:          (id)       => api.delete(`/tontines/${id}`),
  addParticipant:   (id, data) => api.post(`/tontines/${id}/participants`, data),
  removeParticipant:(tontineId, participantId) =>
    api.delete(`/tontines/${tontineId}/participants/${participantId}`),
}
