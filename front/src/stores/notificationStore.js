import { defineStore } from 'pinia'
import { ref } from 'vue'
import notificationApi from '../api/notificationApi'

export const useNotificationStore = defineStore('notification', () => {

  const notifications = ref([])
  const unreadCount   = ref(0)
  const loading       = ref(false)

  // Charger le nombre de non lues (appelé au montage de la sidebar)
  async function fetchUnreadCount() {
    try {
      const res     = await notificationApi.getUnreadCount()
      unreadCount.value = res.data.count
    } catch { /* silencieux */ }
  }

  // Charger toutes les notifications (au clic sur la cloche)
  async function fetchNotifications() {
    loading.value = true
    try {
      const res         = await notificationApi.getAll()
      notifications.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  // Marquer une notification comme lue
  async function markRead(id) {
    await notificationApi.markRead(id)
    // Mettre à jour localement sans recharger
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read_at = new Date().toISOString()
    if (unreadCount.value > 0) unreadCount.value--
  }

  // Tout marquer comme lu
  async function markAllRead() {
    await notificationApi.markAllRead()
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
    unreadCount.value = 0
  }

  return {
    notifications, unreadCount, loading, 
    fetchUnreadCount, fetchNotifications, markRead, markAllRead,
  }
})