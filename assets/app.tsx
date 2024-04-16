import * as React from "react"
import * as ReactDOM from "react-dom/client"
import {
    RouterProvider,
} from "react-router-dom"
import './styles/app.scss'
import 'semantic-ui-css/semantic.min.css'
import router from './routes/router'
import { GoogleOAuthProvider } from "@react-oauth/google"

ReactDOM.createRoot(document.getElementById("root")).render(
    <React.StrictMode>
        <GoogleOAuthProvider clientId="769722632491-67se6s8oujt54d90tnakekk27jbj1hii.apps.googleusercontent.com">
            <RouterProvider router={router} />
        </GoogleOAuthProvider>
        {/* <RouterProvider router={router} /> */}
    </React.StrictMode>
);