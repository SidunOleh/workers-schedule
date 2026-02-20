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

export {
    formatDate,
}