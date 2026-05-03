import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export type NotificationType = 'info' | 'success' | 'warning' | 'error'

export interface AppNotification {
  id: string
  title: string
  message: string
  type: NotificationType
  read: boolean
  time: string
  data?: Record<string, unknown>
}

const notifications = ref<AppNotification[]>([])
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

function mapNotification(notification: any): AppNotification {
  return {
    id: String(notification.id),
    title: notification.title ?? 'Notificación',
    message: notification.message ?? '',
    type: notification.type ?? 'info',
    read: Boolean(notification.read),
    time: notification.time ?? 'Ahora',
    data: notification.data ?? {},
  }
}

async function fetchNotifications() {
  const response = await apiClient.get<{ notifications: AppNotification[]; unread_count: number }>('/notifications')
  notifications.value = response.data.notifications.map(mapNotification)
}

export function useNotifications() {
  async function refreshNotifications() {
    await fetchNotifications()
  }

  async function markAllRead() {
    await apiClient.patch('/notifications/read-all')
    notifications.value.forEach(n => (n.read = true))
  }

  async function markRead(id: string) {
    await apiClient.patch(`/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = true
  }

  return { notifications, unreadCount, markAllRead, markRead, refreshNotifications }
}
