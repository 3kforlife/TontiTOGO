import api from './axios'

export const contributionService = {
  list:        (params) => api.get('/contributions', { params }),
  show:        (id)     => api.get(`/contributions/${id}`),
  exportPdf:   (params) => api.get('/contributions/export/pdf', { params, responseType: 'blob' }),
  exportExcel: (params) => api.get('/contributions/export/excel', { params, responseType: 'blob' }),
}
