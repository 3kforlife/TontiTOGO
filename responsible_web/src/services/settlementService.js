import api from './axios'

export const settlementService = {
  list:           (params) => api.get('/settlements', { params }),
  pendingSummary: (date)   => api.get('/settlements/pending-summary', { params: { date } }),
  validate:       (data)   => api.post('/settlements/validate', data),
}
