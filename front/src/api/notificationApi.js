import apiClient from './axios'

const notificationApi = {
  getAll(page = 1)  { return apiClient.get(`/api/notifications?page=${page}`) },
  getUnreadCount()  { return apiClient.get('/api/notifications/unread-count') },
  markRead(id)      { return apiClient.patch(`/api/notifications/${id}/read`) },
  markAllRead()     { return apiClient.patch('/api/notifications/read-all') },
}

export default notificationApi