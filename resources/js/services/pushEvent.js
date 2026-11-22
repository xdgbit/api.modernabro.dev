import pusher from './pusher'
console.log('Imported Pusher:', pusher)
export function subscribeExpertBadgeUpdates(callback)
{
    const channelName = window.expert_common_channel

    if (!channelName)
    {
        console.error('نام کانال تنظیم نشده است')
        return () => {}
    }

    const channel = pusher.subscribe(channelName)

    const handler = (res) => {
        console.log('ExpertBadgeUpdate RECEIVED:', res)
        callback(res)
    }

    channel.bind('.ExpertBadgeUpdate', handler)
    channel.bind('pusher:subscription_succeeded', () => {
        console.log('Channel subscription succeeded:', channelName)
    })

    channel.bind('pusher:subscription_error', (error) => {
        console.error('Channel subscription failed:', error)
    })

    channel.bind('ExpertBadgeUpdate', handler)
    console.log('Subscribed:', channelName)

    return () =>
    {
        channel.unbind('ExpertBadgeUpdate', handler)
    }
}
