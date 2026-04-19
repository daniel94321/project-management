import { ref, computed } from 'vue'

export type NotificationType = 'info' | 'success' | 'warning' | 'error'

export interface AppNotification {
  id: number
  title: string
  message: string
  type: NotificationType
  read: boolean
  time: string
}

const notifications = ref<AppNotification[]>([
  {
    id: 1,
    title: 'Sesión iniciada',
    message: 'Has ingresado como Super Administrador.',
    type: 'info',
    read: false,
    time: 'Ahora',
  },
  {
    id: 2,
    title: 'Acceso total habilitado',
    message: 'Tienes permisos completos sobre el sistema.',
    type: 'success',
    read: false,
    time: 'Ahora',
  },
])

let nextId = 3

export function useNotifications() {
  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  function markAllRead() {
    notifications.value.forEach(n => (n.read = true))
  }

  function markRead(id: number) {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = true
  }

  function addNotification(data: Omit<AppNotification, 'id' | 'read' | 'time'>) {
    notifications.value.unshift({
      ...data,
      id: nextId++,
      read: false,
      time: 'Ahora',
    })
  }

  return { notifications, unreadCount, markAllRead, markRead, addNotification }
}
