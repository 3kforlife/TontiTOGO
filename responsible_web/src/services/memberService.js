import api from './axios'

export const memberService = {
  list:         (params)   => api.get('/members', { params }),
  show:         (id)       => api.get(`/members/${id}`),
  create:       (data)     => api.post('/members', data),
  update:       (id, data) => api.put(`/members/${id}`, data),
  toggleStatus: (id)       => api.patch(`/members/${id}/toggle-status`),
  destroy:      (id)       => api.delete(`/members/${id}`),
}
