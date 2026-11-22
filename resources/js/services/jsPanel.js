import {
    createVNode,
    render,
} from 'vue'

import {
    jsPanel,
} from 'jspanel4'


/*
|--------------------------------------------------------------------------
| Main Vue App
|--------------------------------------------------------------------------
*/

let mainVueApp = null


/*
|--------------------------------------------------------------------------
| Set Main Vue App
|--------------------------------------------------------------------------
*/

export function setJsPanelVueApp(app) {

    mainVueApp = app

}


/*
|--------------------------------------------------------------------------
| JsPanel V4
|--------------------------------------------------------------------------
*/

export default function JsPanelV4(params = {}) {
    const {
        component = null,
        props = {},
        variableName =
            'jsPanelVariable' +
            Math.random()
                .toString(30)
                .substring(2, 12),
        theme = 'info',
        position = 'center',
        z_index = 10000000,
        headerTitle = 'عنوان',
        just_one_panel = false,
        container = 'window',
        reload = false,
        panelSize = {
            width: () => window.innerWidth * 0.85,
            height: () => window.innerHeight * 0.80,
        },
    } = params
    if (
        just_one_panel &&
        window[variableName]
    ) {

        window[
            variableName
            ].close()

        window[
            variableName
            ] = undefined
    }
    if (!mainVueApp) {

        console.error(
            'JsPanelV4: Vue App is not registered.'
        )

        return null
    }
    const content =
        document.createElement('div')
    content.style.width = '100%'
    content.style.height = '100%'
    const renderComponent = () => {
        if (!component) {
            return null
        }

        const vnode = createVNode(
            component,
            props
        )


        vnode.appContext =
            mainVueApp._context


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        render(
            vnode,
            content
        )


        return vnode

    }


    /*
    |--------------------------------------------------------------------------
    | Unmount Component
    |--------------------------------------------------------------------------
    */

    const unmountComponent = () => {

        render(
            null,
            content
        )

    }


    /*
    |--------------------------------------------------------------------------
    | Panel
    |--------------------------------------------------------------------------
    */

    const panel = jsPanel.create({

        container,

        position,

        theme,

        zIndex: z_index,

        headerTitle,

        closeOnEscape: true,

        panelSize,

        content,


        /*
        |--------------------------------------------------------------------------
        | Callback
        |--------------------------------------------------------------------------
        */

        callback() {

            const panelInstance = this


            /*
            |--------------------------------------------------------------------------
            | Vue Component
            |--------------------------------------------------------------------------
            */

            if (component) {

                renderComponent()

            }


            /*
            |--------------------------------------------------------------------------
            | Refresh
            |--------------------------------------------------------------------------
            */

            if (reload) {

                panelInstance.addControl({

                    html: `
                        <i
                            class="fa-solid fa-arrows-rotate"
                            style="cursor:pointer"
                        ></i>
                    `,

                    name: 'refresh',

                    position: 1,


                    handler() {

                        /*
                        |--------------------------------------------------------------------------
                        | Remove Current Component
                        |--------------------------------------------------------------------------
                        */

                        unmountComponent()


                        /*
                        |--------------------------------------------------------------------------
                        | Render Again
                        |--------------------------------------------------------------------------
                        */

                        renderComponent()

                    }

                })

            }

        },


        /*
        |--------------------------------------------------------------------------
        | Close
        |--------------------------------------------------------------------------
        */

        onclosed() {

            /*
            |--------------------------------------------------------------------------
            | Unmount Vue Component
            |--------------------------------------------------------------------------
            */

            unmountComponent()


            /*
            |--------------------------------------------------------------------------
            | Remove Global Variable
            |--------------------------------------------------------------------------
            */

            window[
                variableName
                ] = undefined

        }

    })


    /*
    |--------------------------------------------------------------------------
    | Global Variable
    |--------------------------------------------------------------------------
    */

    window[
        variableName
        ] = panel


    return panel

}