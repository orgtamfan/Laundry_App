const apiRoot = import.meta.env.DEV ? '/api' : '/Laundry_App/app/api'

export function apiUrl(path) {
  return `${apiRoot}/${path}`
}

export async function readApiResponse(response) {
  const payload = await response.json()
  if (!response.ok) throw new Error(payload.error || 'Permintaan tidak berhasil.')
  return payload
}

export async function requestApi(path, options = {}) {
  const response = await fetch(apiUrl(path), {
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
    },
    ...options,
  })
  return readApiResponse(response)
}