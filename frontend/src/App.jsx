import { useEffect, useState } from 'react'
import Operations from './Operations'
import { apiUrl, readApiResponse } from './api'
import './App.css'

function LoginScreen({ onLogin, initialError }) {
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [pending, setPending] = useState(false)
  const [error, setError] = useState('')
  const visibleError = error || initialError

  async function handleSubmit(event) {
    event.preventDefault()
    setPending(true)
    setError('')
    try {
      await onLogin({ username, password })
    } catch (loginError) {
      setError(loginError instanceof Error ? loginError.message : 'Gagal masuk. Coba lagi.')
    } finally {
      setPending(false)
    }
  }

  return (
    <main className="auth-page">
      <div className="auth-layout">
        <section className="auth-intro" aria-labelledby="auth-title">
          <div className="auth-brand"><span className="auth-brand-mark">RL</span><span>RUMAH <strong>LAUNDRY</strong></span></div>
          <div className="auth-intro-copy">
            <p className="auth-eyebrow">OPERASIONAL OUTLET</p>
            <h1 id="auth-title">Semua urusan<br /><span>laundry, tertata.</span></h1>
            <p>Masuk untuk mengelola aktivitas dan pesanan outlet.</p>
          </div>
          <div className="auth-signature"><span>BANDAR LAMPUNG</span><span>RL / 01</span></div>
        </section>

        <section className="auth-panel" aria-labelledby="login-heading">
          <p className="auth-kicker">AKSES ADMINISTRATOR</p>
          <h2 id="login-heading">Selamat datang</h2>
          <p className="auth-description">Gunakan akun pengelola untuk melanjutkan.</p>
          <form className="auth-form" onSubmit={handleSubmit}>
            <label htmlFor="username">Username</label>
            <input
              id="username"
              name="username"
              type="text"
              autoComplete="username"
              autoCapitalize="none"
              spellCheck="false"
              value={username}
              onChange={(event) => setUsername(event.target.value)}
              required
            />
            <label htmlFor="password">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              autoComplete="current-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              required
            />
            {visibleError && <p className="auth-error" role="alert">{visibleError}</p>}
            <button className="auth-submit" type="submit" disabled={pending}>
              {pending ? 'Memeriksa akun...' : 'Masuk ke dashboard'}
              <span aria-hidden="true">→</span>
            </button>
          </form>
          <p className="auth-footnote">Masuk dengan akun administrator atau petugas.</p>
        </section>
      </div>
    </main>
  )
}

function App() {
  const [user, setUser] = useState(null)
  const [authLoading, setAuthLoading] = useState(true)
  const [authError, setAuthError] = useState('')

  useEffect(() => {
    let cancelled = false

    async function checkSession() {
      try {
        const payload = await readApiResponse(await fetch(apiUrl('auth.php'), {
          headers: { Accept: 'application/json' },
        }))
        if (!cancelled && payload.authenticated) setUser(payload.user)
      } catch (sessionError) {
        if (!cancelled) {
          setAuthError(sessionError instanceof Error ? sessionError.message : 'Gagal memeriksa sesi.')
        }
      } finally {
        if (!cancelled) setAuthLoading(false)
      }
    }

    checkSession()
    return () => { cancelled = true }
  }, [])

  async function handleLogin(credentials) {
    const payload = await readApiResponse(await fetch(apiUrl('auth.php'), {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(credentials),
    }))
    setAuthError('')
    setUser(payload.user)
  }

  async function handleLogout() {
    await readApiResponse(await fetch(apiUrl('auth.php'), {
      method: 'DELETE',
      headers: { Accept: 'application/json' },
    }))
    setUser(null)
  }

  if (authLoading) {
    return <main className="auth-loading" aria-live="polite">Memeriksa sesi administrator...</main>
  }

  if (!user) return <LoginScreen onLogin={handleLogin} initialError={authError} />
  return <Operations user={user} onLogout={handleLogout} />
}

export default App
