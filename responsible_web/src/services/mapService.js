import api from './axios'

export const mapService = {
  markers: (params) => api.get('/map/markers', { params }),
}
