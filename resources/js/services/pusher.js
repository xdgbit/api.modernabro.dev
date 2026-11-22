import Pusher from 'pusher-js'

const options = window.pusher_options

if (!options?.key) {
    throw new Error('Pusher key تنظیم نشده است')
}

const useTLS =
    options.use_tls ??
    options.scheme === 'https'

const pusher = new Pusher(options.key, {
    cluster: options.cluster,
    forceTLS: useTLS,

    ...(options.host
        ? {
            wsHost: options.host,
            wsPort: Number(options.port),
            wssPort: Number(options.port),
        }
        : {}),

    enabledTransports: ['ws', 'wss'],
    disableStats: true,
})

window.pusher_connection = pusher

console.log('Pusher options:', options)
console.log('Pusher instance:', pusher)

pusher.connection.bind('state_change', (states) => {
    console.log(
        'Pusher state:',
        states.previous,
        '->',
        states.current
    )
})

pusher.connection.bind('error', (error) => {
    console.error('Pusher connection error:', error)
})

export default pusher