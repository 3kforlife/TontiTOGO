import api from './axios'

export const authService = {
  register:   (data) => api.post('/register', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  login:      (data) => api.post('/login', data),
  logout:     ()     => api.post('/logout'),
  me:         ()     => api.get('/me'),

  // PUT /profile via POST + _method:PUT (multipart/form-data compatible)
  updateProfile: (data) => api.post('/profile', data, { headers: { 'Content-Type': 'multipart/form-data' } }),

  // Changement de mot de passe depuis le profil (current_password + password + password_confirmation)
  changePassword: (data) => api.put('/profile', data),

  deleteAccount:  (data) => api.delete('/account', { data }),

  sendVerificationEmail: () => api.post('/email/verification-notification'),
  forgotPassword: (data)    => api.post('/password/forgot', data),
  resetPassword:  (data)    => api.post('/password/reset', data),
}
