import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

export function useToast() {
  function add(message, type = 'success', duration = 3500) {
    const id = ++nextId
    toasts.value.push({ id, message, type })
    setTimeout(() => remove(id), duration)
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (msg) => add(msg, 'success')
  const error   = (msg) => add(msg, 'error', 5000)
  const info    = (msg) => add(msg, 'info')

  return { toasts, success, error, info, remove }
}
