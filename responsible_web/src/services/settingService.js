import api from './axios'

export const settingService = {
  get:    ()     => api.get('/settings'),
  update: (data) => api.put('/settings', data),
}
