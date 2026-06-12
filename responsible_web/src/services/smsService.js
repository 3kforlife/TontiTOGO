import api from './axios'

export const smsService = {
  list:          (params) => api.get('/sms', { params }),
  show:          (id)     => api.get(`/sms/${id}`),
  sendReminders: ()       => api.post('/sms/send-reminders'),
}
