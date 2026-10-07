import { useEffect, useState } from 'react'
import { requestApi } from './api'
import './Operations.css'

const currency = new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
})
const shortDate = new Intl.DateTimeFormat('id-ID', {
  day: 'numeric', month: 'short', year: 'numeric',
})
const todayLabel = new Intl.DateTimeFormat('id-ID', {
  weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
}).format(new Date())

function localDateValue() {
  const now = new Date()
  return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10)
}

function displayDate(value) {
  if (!value) return '-'
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return value
  return shortDate.format(new Date(`${value}T00:00:00`))
}

async function fetchWorkspace(isAdmin) {
  const resources = ['orders', 'packages', 'history', ...(isAdmin ? ['employees'] : [])]
  const results = await Promise.all(resources.map(async (resource) => {
    const payload = await requestApi(`operations.php?resource=${resource}`)
    return [resource, payload.items]
  }))
  return Object.fromEntries(results)
}

function Modal({ title, onClose, children }) {
  return (
    <div className="operation-overlay" onMouseDown={(event) => event.target === event.currentTarget && onClose()}>
      <section className="operation-modal" role="dialog" aria-modal="true" aria-labelledby="operation-modal-title">
        <header className="operation-modal-header">
          <h2 id="operation-modal-title">{title}</h2>
          <button className="icon-button" type="button" onClick={onClose} aria-label="Tutup">×</button>
        </header>
        {children}
      </section>
    </div>
  )
}

function OrderTable({ orders, onDetails, onPay, onCancel }) {
  if (!orders.length) return <div className="operation-empty">Belum ada order untuk ditampilkan.</div>
  return (
    <div className="operation-table-wrap">
      <table className="operation-table">
        <thead><tr><th>NO. ORDER</th><th>PELANGGAN</th><th>LAYANAN</th><th>TGL MASUK</th><th>TOTAL</th><th>STATUS</th><th>AKSI</th></tr></thead>
        <tbody>{orders.map((order) => (
          <tr key={`${order.type}-${order.code}`}>
            <td className="operation-code">{order.code}</td>
            <td className="operation-primary-cell">{order.customer}</td>
            <td>{order.service}</td>
            <td>{displayDate(order.received)}</td>
            <td>{currency.format(order.total)}</td>
            <td><span className={`operation-status ${order.status === 'Siap diambil' ? 'is-ready' : 'is-processing'}`}>{order.status}</span></td>
            <td><div className="row-actions">
              <button type="button" onClick={() => onDetails(order)}>Detail</button>
              <button type="button" onClick={() => onPay(order)}>Bayar</button>
              <button className="danger-action" type="button" onClick={() => onCancel(order)}>Batalkan</button>
            </div></td>
          </tr>
        ))}</tbody>
      </table>
    </div>
  )
}

function OrderForm({ packages, busy, onClose, onSave }) {
  const today = localDateValue()
  const [form, setForm] = useState({
    type: 'ck', packageId: '', customer: '', phone: '', address: '', quantity: '1',
    received: today, dueDate: today, notes: '',
  })
  const choices = packages.filter((item) => item.type === form.type)
  const selectedPackage = choices.find((item) => item.id === Number(form.packageId))
  const estimatedTotal = selectedPackage ? Number(form.quantity || 0) * selectedPackage.price : 0

  function update(field, value) {
    setForm((current) => ({ ...current, [field]: value }))
  }

  function changeType(type) {
    setForm((current) => ({ ...current, type, packageId: '' }))
  }

  function submit(event) {
    event.preventDefault()
    onSave({
      action: 'order.create', ...form,
      packageId: Number(form.packageId), quantity: Number(form.quantity),
    })
  }

  return (
    <form className="operation-form" onSubmit={submit}>
      <div className="operation-form-grid">
        <label>Jenis layanan<select value={form.type} onChange={(event) => changeType(event.target.value)}><option value="ck">Cuci komplit</option><option value="dc">Dry clean</option><option value="cs">Cuci satuan</option></select></label>
        <label>Paket<select value={form.packageId} onChange={(event) => update('packageId', event.target.value)} required><option value="">Pilih paket</option>{choices.map((item) => <option key={item.id} value={item.id}>{item.name} · {currency.format(item.price)} / {item.type === 'cs' ? 'pcs' : 'kg'}</option>)}</select></label>
        <label>Nama pelanggan<input value={form.customer} onChange={(event) => update('customer', event.target.value)} required maxLength="100" /></label>
        <label>Nomor telepon<input value={form.phone} onChange={(event) => update('phone', event.target.value)} maxLength="30" /></label>
        <label>Jumlah ({form.type === 'cs' ? 'pcs' : 'kg'})<input type="number" min="1" step={form.type === 'cs' ? '1' : '0.1'} value={form.quantity} onChange={(event) => update('quantity', event.target.value)} required /></label>
        <label>Tanggal masuk<input type="date" value={form.received} onChange={(event) => update('received', event.target.value)} required /></label>
        <label>Tanggal selesai<input type="date" min={form.received} value={form.dueDate} onChange={(event) => update('dueDate', event.target.value)} required /></label>
        <label className="form-wide">Alamat<textarea rows="2" value={form.address} onChange={(event) => update('address', event.target.value)} /></label>
        <label className="form-wide">Keterangan<textarea rows="2" value={form.notes} onChange={(event) => update('notes', event.target.value)} /></label>
      </div>
      <div className="estimate-line"><span>Estimasi total</span><strong>{currency.format(estimatedTotal)}</strong></div>
      <div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Batal</button><button className="primary-button" type="submit" disabled={busy || !selectedPackage}>{busy ? 'Menyimpan...' : 'Simpan order'}</button></div>
    </form>
  )
}

function PaymentForm({ order, busy, onClose, onPay }) {
  const [paid, setPaid] = useState(String(order.total))
  const amount = Number(paid || 0)
  const changeAmount = Math.max(0, amount - order.total)

  function submit(event) {
    event.preventDefault()
    onPay({ action: 'order.pay', type: order.type, code: order.code, paid: amount })
  }

  return (
    <form className="operation-form" onSubmit={submit}>
      <div className="payment-summary"><span>{order.code} · {order.customer}</span><strong>{currency.format(order.total)}</strong></div>
      <label>Nominal dibayar<input type="number" min={order.total} step="1" value={paid} onChange={(event) => setPaid(event.target.value)} required /></label>
      <div className="estimate-line"><span>Kembalian</span><strong>{currency.format(changeAmount)}</strong></div>
      <div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Batal</button><button className="primary-button" type="submit" disabled={busy || amount < order.total}>{busy ? 'Memproses...' : 'Catat pembayaran'}</button></div>
    </form>
  )
}

function PackageForm({ item, busy, onClose, onSave }) {
  const [form, setForm] = useState(item || { type: 'ck', name: '', duration: '', minimum: 1, price: 0, description: '-' })
  function update(field, value) {
    setForm((current) => ({ ...current, [field]: value }))
  }
  function submit(event) {
    event.preventDefault()
    onSave({ action: 'package.save', ...form, id: item?.id || 0, minimum: Number(form.minimum), price: Number(form.price) })
  }

  return (
    <form className="operation-form" onSubmit={submit}>
      <div className="operation-form-grid">
        <label>Jenis layanan<select value={form.type} onChange={(event) => update('type', event.target.value)} disabled={Boolean(item)}><option value="ck">Cuci komplit</option><option value="dc">Dry clean</option><option value="cs">Cuci satuan</option></select></label>
        <label>Nama paket<input value={form.name} onChange={(event) => update('name', event.target.value)} required maxLength="100" /></label>
        <label>Durasi kerja<input value={form.duration} onChange={(event) => update('duration', event.target.value)} required maxLength="50" placeholder="Contoh: 2 Hari" /></label>
        <label>Jumlah minimum ({form.type === 'cs' ? 'pcs' : 'kg'})<input type="number" min="0" step={form.type === 'cs' ? '1' : '0.1'} value={form.minimum} onChange={(event) => update('minimum', event.target.value)} required /></label>
        <label>Tarif per {form.type === 'cs' ? 'pcs' : 'kg'}<input type="number" min="0" step="1" value={form.price} onChange={(event) => update('price', event.target.value)} required /></label>
        {form.type === 'cs' && <label className="form-wide">Keterangan<input value={form.description} onChange={(event) => update('description', event.target.value)} maxLength="255" /></label>}
      </div>
      <div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Batal</button><button className="primary-button" type="submit" disabled={busy}>{busy ? 'Menyimpan...' : 'Simpan paket'}</button></div>
    </form>
  )
}

function EmployeeForm({ item, busy, onClose, onSave }) {
  const [form, setForm] = useState(() => item ? { ...item, password: '' } : { name: '', email: '', username: '', password: '', role: 'Karyawan' })
  function update(field, value) {
    setForm((current) => ({ ...current, [field]: value }))
  }
  function submit(event) {
    event.preventDefault()
    onSave({ action: 'employee.save', ...form, id: item?.id || 0 })
  }

  return (
    <form className="operation-form" onSubmit={submit}>
      <div className="operation-form-grid">
        <label>Nama lengkap<input value={form.name} onChange={(event) => update('name', event.target.value)} required maxLength="100" /></label>
        <label>Email<input type="email" value={form.email} onChange={(event) => update('email', event.target.value)} required maxLength="100" /></label>
        <label>Username<input value={form.username} onChange={(event) => update('username', event.target.value)} required maxLength="50" autoCapitalize="none" /></label>
        <label>Peran<select value={form.role} onChange={(event) => update('role', event.target.value)}><option value="Karyawan">Karyawan</option><option value="User">User</option><option value="Admin">Admin</option></select></label>
        <label className="form-wide">{item ? 'Password baru (kosongkan agar tetap sama)' : 'Password'}<input type="password" autoComplete="new-password" value={form.password} onChange={(event) => update('password', event.target.value)} required={!item} minLength="6" /></label>
      </div>
      <div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Batal</button><button className="primary-button" type="submit" disabled={busy}>{busy ? 'Menyimpan...' : 'Simpan akun'}</button></div>
    </form>
  )
}

export default function Operations({ user, onLogout }) {
  const [view, setView] = useState('overview')
  const [orders, setOrders] = useState([])
  const [packages, setPackages] = useState([])
  const [employees, setEmployees] = useState([])
  const [history, setHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')
  const [notice, setNotice] = useState('')
  const [dialog, setDialog] = useState(null)
  const [search, setSearch] = useState('')
  const [status, setStatus] = useState('Semua status')
  const [serviceFilter, setServiceFilter] = useState('Semua layanan')
  const [loggingOut, setLoggingOut] = useState(false)
  const isAdmin = user.role === 'Admin'
  const navigation = [
    { id: 'overview', number: '01', label: 'Ringkasan' },
    { id: 'orders', number: '02', label: 'Order' },
    { id: 'packages', number: '03', label: 'Paket' },
    ...(isAdmin ? [{ id: 'employees', number: '04', label: 'Karyawan' }] : []),
    { id: 'history', number: isAdmin ? '05' : '04', label: 'Riwayat' },
  ]

  async function loadData() {
    setError('')
    try {
      const data = await fetchWorkspace(isAdmin)
      setOrders(data.orders || [])
      setPackages(data.packages || [])
      setHistory(data.history || [])
      if (isAdmin) setEmployees(data.employees || [])
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : 'Gagal memuat data aplikasi.')
    }
  }

  useEffect(() => {
    let cancelled = false
    fetchWorkspace(isAdmin)
      .then((data) => {
        if (cancelled) return
        setOrders(data.orders || [])
        setPackages(data.packages || [])
        setHistory(data.history || [])
        if (isAdmin) setEmployees(data.employees || [])
      })
      .catch((loadError) => {
        if (!cancelled) setError(loadError instanceof Error ? loadError.message : 'Gagal memuat data aplikasi.')
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })
    return () => { cancelled = true }
  }, [isAdmin])

  async function saveAction(payload) {
    setBusy(true)
    setError('')
    setNotice('')
    try {
      const result = await requestApi('operations.php', {
        method: 'POST', body: JSON.stringify(payload),
      })
      setNotice(result.message || 'Perubahan berhasil disimpan.')
      setDialog(null)
      await loadData()
    } catch (actionError) {
      setError(actionError instanceof Error ? actionError.message : 'Aksi gagal diproses.')
    } finally {
      setBusy(false)
    }
  }

  async function logout() {
    setLoggingOut(true)
    try {
      await onLogout()
    } finally {
      setLoggingOut(false)
    }
  }

  const today = localDateValue()
  const todaysOrders = orders.filter((order) => order.received === today).length
  const processingOrders = orders.filter((order) => order.status === 'Diproses').length
  const readyOrders = orders.filter((order) => order.status === 'Siap diambil').length
  const activeValue = orders.reduce((total, order) => total + order.total, 0)
  const visibleOrders = orders.filter((order) => {
    const matchesSearch = `${order.code} ${order.customer} ${order.service}`.toLowerCase().includes(search.toLowerCase())
    return matchesSearch && (status === 'Semua status' || order.status === status)
  })
  const visiblePackages = packages.filter((item) => {
    const matchesSearch = `${item.name} ${item.service}`.toLowerCase().includes(search.toLowerCase())
    return matchesSearch && (serviceFilter === 'Semua layanan' || item.type === serviceFilter)
  })
  const visibleEmployees = employees.filter((item) => `${item.name} ${item.username} ${item.email}`.toLowerCase().includes(search.toLowerCase()))
  const visibleHistory = history.filter((item) => `${item.code} ${item.customer} ${item.service}`.toLowerCase().includes(search.toLowerCase()))
  const pageName = navigation.find((item) => item.id === view)?.label || 'Ringkasan'

  function openOrderDetails(order) {
    setDialog({ kind: 'order-details', item: order })
  }

  function openCancel(order) {
    setDialog({ kind: 'cancel-order', item: order })
  }

  function renderOrderTable(rows) {
    return <OrderTable orders={rows} onDetails={openOrderDetails} onPay={(item) => setDialog({ kind: 'payment', item })} onCancel={openCancel} />
  }

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <a className="brand" href="#overview" onClick={() => setView('overview')} aria-label="Rumah Laundry, beranda">
          <span className="brand-mark">RL</span><span className="brand-name">rumah<span>laundry</span></span>
        </a>
        <div className="side-label">MENU UTAMA</div>
        <nav className="navigation operations-nav" aria-label="Navigasi utama">
          {navigation.map((item) => <button className={`nav-link ${view === item.id ? 'active' : ''}`} key={item.id} type="button" onClick={() => { setView(item.id); setSearch('') }}><span className="nav-glyph">{item.number}</span>{item.label}</button>)}
        </nav>
        <div className="sidebar-bottom"><div className="outlet-dot" /><div><strong>Outlet utama</strong><span>Bandar Lampung</span></div><span className="online-dot" aria-label="Online" /></div>
      </aside>

      <main className="main-content">
        <header className="topbar">
          <div className="breadcrumb">Operasional <span>/</span> {pageName}</div>
          <div className="user-menu"><span className="user-avatar">{(user.name || 'A').slice(0, 1).toUpperCase()}</span><span><strong>{user.name}</strong><small>{isAdmin ? 'Administrator' : user.role === 'Karyawan' ? 'Karyawan' : 'Pengguna'}</small></span><button className="logout-button" type="button" onClick={logout} disabled={loggingOut} aria-label="Keluar dari akun" title="Keluar"><span aria-hidden="true">↪</span><span>{loggingOut ? 'Keluar...' : 'Keluar'}</span></button></div>
        </header>

        <div className="page-content">
          {notice && <div className="operation-notice" role="status"><span>{notice}</span><button type="button" onClick={() => setNotice('')} aria-label="Tutup notifikasi">×</button></div>}
          {error && <div className="operation-error" role="alert"><span>{error}</span><button type="button" onClick={() => setError('')} aria-label="Tutup pesan">×</button></div>}
          {loading ? <div className="operation-loading">Memuat data operasional...</div> : <>
            {view === 'overview' && <>
              <section className="page-heading"><div><p className="eyebrow">{todayLabel}</p><h1>Ringkasan operasional</h1><p className="subheading">Pantau aktivitas laundry hari ini.</p></div><button className="primary-button" type="button" onClick={() => setDialog({ kind: 'order-form' })}><span>+</span> Order baru</button></section>
              <section className="stats-grid" aria-label="Statistik ringkas">
                <StatCard title="Order hari ini" value={todaysOrders} detail="Order aktif masuk hari ini" tone="blue" />
                <StatCard title="Perlu diproses" value={processingOrders} detail="Belum mencapai tanggal selesai" tone="coral" />
                <StatCard title="Siap diambil" value={readyOrders} detail="Tanggal selesai tercapai" tone="yellow" />
                <StatCard title="Nilai order aktif" value={currency.format(activeValue)} detail="Belum termasuk transaksi lunas" tone="green" />
              </section>
              <section className="content-panel"><div className="panel-heading"><div><p className="eyebrow">AKTIVITAS TERBARU</p><h2>Order aktif</h2></div><span className="sample-label live-label"><span /> DATA LIVE</span></div>{renderOrderTable(orders.slice(0, 6))}<footer className="panel-footer"><span>{orders.length} order aktif · {packages.length} paket tersedia</span><button type="button" onClick={() => setView('orders')}>Buka daftar order <span>→</span></button></footer></section>
            </>}

            {view === 'orders' && <>
              <section className="page-heading"><div><p className="eyebrow">OPERASIONAL</p><h1>Daftar order</h1><p className="subheading">Buat, periksa, batalkan, atau catat pembayaran.</p></div><button className="primary-button" type="button" onClick={() => setDialog({ kind: 'order-form' })}><span>+</span> Order baru</button></section>
              <section className="content-panel"><div className="panel-heading"><div><p className="eyebrow">ORDER AKTIF</p><h2>{orders.length} pesanan</h2></div><span className="sample-label live-label"><span /> DATABASE</span></div><div className="table-controls"><SearchInput value={search} onChange={setSearch} placeholder="Cari nomor order atau pelanggan" /><select value={status} onChange={(event) => setStatus(event.target.value)} aria-label="Filter status"><option>Semua status</option><option>Diproses</option><option>Siap diambil</option></select></div>{renderOrderTable(visibleOrders)}</section>
            </>}

            {view === 'packages' && <>
              <section className="page-heading"><div><p className="eyebrow">KATALOG LAYANAN</p><h1>Daftar paket</h1><p className="subheading">Atur tarif, durasi, dan jumlah minimum layanan.</p></div>{isAdmin && <button className="primary-button" type="button" onClick={() => setDialog({ kind: 'package-form' })}><span>+</span> Paket baru</button>}</section>
              <section className="content-panel"><div className="panel-heading"><div><p className="eyebrow">PAKET AKTIF</p><h2>{packages.length} paket tersedia</h2></div></div><div className="table-controls"><SearchInput value={search} onChange={setSearch} placeholder="Cari nama paket" /><select value={serviceFilter} onChange={(event) => setServiceFilter(event.target.value)} aria-label="Filter layanan"><option value="Semua layanan">Semua layanan</option><option value="ck">Cuci komplit</option><option value="dc">Dry clean</option><option value="cs">Cuci satuan</option></select></div><PackageTable packages={visiblePackages} canManage={isAdmin} onEdit={(item) => setDialog({ kind: 'package-form', item })} onDelete={(item) => setDialog({ kind: 'delete-package', item })} /></section>
            </>}

            {view === 'employees' && isAdmin && <>
              <section className="page-heading"><div><p className="eyebrow">AKSES APLIKASI</p><h1>Data karyawan</h1><p className="subheading">Kelola akun administrator dan petugas outlet.</p></div><button className="primary-button" type="button" onClick={() => setDialog({ kind: 'employee-form' })}><span>+</span> Akun baru</button></section>
              <section className="content-panel"><div className="panel-heading"><div><p className="eyebrow">PENGGUNA</p><h2>{employees.length} akun</h2></div></div><div className="table-controls"><SearchInput value={search} onChange={setSearch} placeholder="Cari nama, username, atau email" /></div><EmployeeTable employees={visibleEmployees} currentId={user.id} onEdit={(item) => setDialog({ kind: 'employee-form', item })} onDelete={(item) => setDialog({ kind: 'delete-employee', item })} /></section>
            </>}

            {view === 'history' && <>
              <section className="page-heading"><div><p className="eyebrow">PEMBAYARAN</p><h1>Riwayat transaksi</h1><p className="subheading">Pembayaran lunas dan detail kembalian.</p></div><button className="secondary-button print-button" type="button" onClick={() => window.print()}>Cetak daftar</button></section>
              <section className="content-panel"><div className="panel-heading"><div><p className="eyebrow">TRANSAKSI LUNAS</p><h2>{history.length} transaksi</h2></div></div><div className="table-controls"><SearchInput value={search} onChange={setSearch} placeholder="Cari nomor transaksi atau pelanggan" /></div><HistoryTable history={visibleHistory} onDetails={(item) => setDialog({ kind: 'history-details', item })} /></section>
            </>}
          </>}
        </div>
      </main>

      {dialog && <OperationDialog dialog={dialog} packages={packages} busy={busy} onClose={() => !busy && setDialog(null)} onSave={saveAction} onShowPayment={(item) => setDialog({ kind: 'payment', item })} onPrint={() => window.print()} />}
    </div>
  )
}

function StatCard({ title, value, detail, tone }) {
  const symbols = { blue: '↗', coral: '!', yellow: '✓', green: 'Rp' }
  return <article className={`stat-card ${tone === 'blue' ? 'stat-mint' : ''}`}><div className="stat-top"><span>{title}</span><span className={`stat-symbol symbol-${tone}`}>{symbols[tone]}</span></div><strong className={tone === 'green' ? 'revenue' : ''}>{value}</strong><div className="stat-foot">{detail}</div>{tone === 'blue' ? <div className="mini-bars" aria-hidden="true"><i /><i /><i /><i /><i /><i /><i /><i /><i /><i /><i /><i /></div> : <div className={`stat-accent accent-${tone}`} />}</article>
}

function SearchInput({ value, onChange, placeholder }) {
  return <label className="search-box"><span aria-hidden="true">⌕</span><input value={value} onChange={(event) => onChange(event.target.value)} placeholder={placeholder} aria-label={placeholder} /></label>
}

function PackageTable({ packages, canManage, onEdit, onDelete }) {
  if (!packages.length) return <div className="operation-empty">Tidak ada paket yang cocok.</div>
  return <div className="operation-table-wrap"><table className="operation-table"><thead><tr><th>PAKET</th><th>LAYANAN</th><th>DURASI</th><th>MINIMUM</th><th>TARIF</th>{canManage && <th>AKSI</th>}</tr></thead><tbody>{packages.map((item) => <tr key={`${item.type}-${item.id}`}><td className="operation-primary-cell">{item.name}{item.description && item.description !== '-' && <small className="cell-secondary">{item.description}</small>}</td><td>{item.service}</td><td>{item.duration}</td><td>{item.minimum} {item.type === 'cs' ? 'pcs' : 'kg'}</td><td>{currency.format(item.price)} / {item.type === 'cs' ? 'pcs' : 'kg'}</td>{canManage && <td><div className="row-actions"><button type="button" onClick={() => onEdit(item)}>Edit</button><button className="danger-action" type="button" onClick={() => onDelete(item)}>Hapus</button></div></td>}</tr>)}</tbody></table></div>
}

function EmployeeTable({ employees, currentId, onEdit, onDelete }) {
  if (!employees.length) return <div className="operation-empty">Belum ada akun.</div>
  return <div className="operation-table-wrap"><table className="operation-table"><thead><tr><th>NAMA</th><th>USERNAME</th><th>EMAIL</th><th>PERAN</th><th>AKSI</th></tr></thead><tbody>{employees.map((item) => <tr key={item.id}><td className="operation-primary-cell">{item.name}{item.id === Number(currentId) && <small className="cell-secondary">Akun aktif</small>}</td><td>{item.username}</td><td>{item.email}</td><td><span className="operation-role">{item.role}</span></td><td><div className="row-actions"><button type="button" onClick={() => onEdit(item)}>Edit</button><button className="danger-action" type="button" disabled={item.id === Number(currentId)} onClick={() => onDelete(item)}>Hapus</button></div></td></tr>)}</tbody></table></div>
}

function HistoryTable({ history, onDetails }) {
  if (!history.length) return <div className="operation-empty">Belum ada transaksi yang cocok.</div>
  return <div className="operation-table-wrap"><table className="operation-table"><thead><tr><th>NO. ORDER</th><th>PELANGGAN</th><th>LAYANAN</th><th>TGL MASUK</th><th>TOTAL</th><th>DIBAYAR</th><th>KEMBALIAN</th><th>AKSI</th></tr></thead><tbody>{history.map((item) => <tr key={`${item.type}-${item.id}`}><td className="operation-code">{item.code}</td><td className="operation-primary-cell">{item.customer}</td><td>{item.service}</td><td>{displayDate(item.received)}</td><td>{currency.format(item.total)}</td><td>{currency.format(item.paid)}</td><td>{currency.format(item.changeAmount)}</td><td><button className="row-link" type="button" onClick={() => onDetails(item)}>Detail</button></td></tr>)}</tbody></table></div>
}

function OperationDialog({ dialog, packages, busy, onClose, onSave, onShowPayment, onPrint }) {
  const item = dialog.item
  if (dialog.kind === 'order-form') return <Modal title="Buat order baru" onClose={onClose}><OrderForm packages={packages} busy={busy} onClose={onClose} onSave={onSave} /></Modal>
  if (dialog.kind === 'payment') return <Modal title="Catat pembayaran" onClose={onClose}><PaymentForm order={item} busy={busy} onClose={onClose} onPay={onSave} /></Modal>
  if (dialog.kind === 'package-form') return <Modal title={item ? 'Edit paket' : 'Tambah paket'} onClose={onClose}><PackageForm item={item} busy={busy} onClose={onClose} onSave={onSave} /></Modal>
  if (dialog.kind === 'employee-form') return <Modal title={item ? 'Edit akun' : 'Tambah akun'} onClose={onClose}><EmployeeForm item={item} busy={busy} onClose={onClose} onSave={onSave} /></Modal>
  if (dialog.kind === 'cancel-order') return <Modal title="Batalkan order" onClose={onClose}><p className="confirm-copy">Batalkan order <strong>{item.code}</strong> atas nama {item.customer}? Data order aktif akan dihapus.</p><div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Kembali</button><button className="danger-button" type="button" disabled={busy} onClick={() => onSave({ action: 'order.cancel', type: item.type, code: item.code })}>{busy ? 'Memproses...' : 'Batalkan order'}</button></div></Modal>
  if (dialog.kind === 'delete-package') return <Modal title="Hapus paket" onClose={onClose}><p className="confirm-copy">Hapus paket <strong>{item.name}</strong>? Order yang sudah dibuat tetap menyimpan nama dan tarifnya.</p><div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Kembali</button><button className="danger-button" type="button" disabled={busy} onClick={() => onSave({ action: 'package.delete', type: item.type, id: item.id })}>{busy ? 'Menghapus...' : 'Hapus paket'}</button></div></Modal>
  if (dialog.kind === 'delete-employee') return <Modal title="Hapus akun" onClose={onClose}><p className="confirm-copy">Hapus akun <strong>{item.name}</strong> ({item.username})?</p><div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Kembali</button><button className="danger-button" type="button" disabled={busy} onClick={() => onSave({ action: 'employee.delete', id: item.id })}>{busy ? 'Menghapus...' : 'Hapus akun'}</button></div></Modal>
  if (dialog.kind === 'order-details') return <Modal title={`Detail order ${item.code}`} onClose={onClose}><DetailList item={item} isHistory={false} /><div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Tutup</button><button className="primary-button" type="button" onClick={() => onShowPayment(item)}>Bayar order</button></div></Modal>
  if (dialog.kind === 'history-details') return <Modal title={`Detail transaksi ${item.code}`} onClose={onClose}><DetailList item={item} isHistory /><div className="operation-modal-actions"><button className="secondary-button" type="button" onClick={onClose}>Tutup</button><button className="primary-button" type="button" onClick={onPrint}>Cetak detail</button></div></Modal>
  return null
}

function DetailList({ item, isHistory }) {
  const rows = [
    ['Pelanggan', item.customer], ['Telepon', item.phone || '-'], ['Layanan', item.service],
    ['Paket', item.package], ['Jumlah', `${item.quantity} ${item.unit}`], ['Tarif satuan', currency.format(item.unitPrice)],
    ['Tanggal masuk', displayDate(item.received)], ['Tanggal selesai', displayDate(item.dueDate)],
    ['Total', currency.format(item.total)], ...(isHistory ? [['Dibayar', currency.format(item.paid)], ['Kembalian', currency.format(item.changeAmount)]] : []),
    ['Alamat', item.address || '-'], ['Keterangan', item.notes || '-'],
  ]
  return <dl className="detail-list">{rows.map(([label, value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}</dl>
}