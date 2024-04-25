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
        <RouterProvider router={router} />
    </React.StrictMode>
);