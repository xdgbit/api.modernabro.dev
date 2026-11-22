import * as PusherPushNotifications from '@pusher/push-notifications-web'

let beamsClient = null

export async function initBeams(interests = [])
{
    if (!window.instanceId)
    {
        throw new Error('Pusher Beams instanceId تنظیم نشده است')
    }

    if (!beamsClient)
    {
        beamsClient = new PusherPushNotifications.Client({
            instanceId: window.instanceId,
        })
    }

    await beamsClient.start()

    const currentInterests = await beamsClient.getDeviceInterests()

    for (const interest of [...new Set(interests.filter(Boolean))])
    {
        if (!currentInterests.includes(interest))
        {
            await beamsClient.addDeviceInterest(interest)
        }
    }

    console.log('Beams فعال شد:', await beamsClient.getDeviceInterests())

    return beamsClient
}