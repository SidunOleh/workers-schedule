import authApi from '../api/auth'

function formatDate(date, withTime = false) {
    const options = {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
    }

    if (withTime) {
        options.hour = '2-digit'
        options.minute = '2-digit'
    }

    return (date instanceof Date ? date : new Date(date)).toLocaleString('en-US', options)
}

function hasRole(roles) {
    const user = authApi.user()

    if (roles.includes(user.role)) {
        return true
    }

    return false
}

function formatToYMDHIS(date, withTime = true) {
    let dateStr = `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    
    if (withTime) {
        dateStr += ` ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`
    }

    return dateStr
}

export {
    formatDate,
    hasRole,
    formatToYMDHIS,   
}