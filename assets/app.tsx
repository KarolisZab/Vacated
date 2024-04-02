import * as React from "react"
import * as ReactDOM from "react-dom/client"
import {
    createBrowserRouter,
    RouterProvider,
} from "react-router-dom"
import './styles/app.scss'
import Root from "./routes/root"
import 'semantic-ui-css/semantic.min.css'
import Home from './routes/home'
import ErrorPage from "./error-page"
import EmployeesList from "./components/EmployeesList"
import Login from "./components/Login"
import Register from "./components/Register"
import EmployeeDetails from "./components/EmployeeDetails"
import EmployeeEdit from "./components/EmployeeEdit"
import PrivateRoute from "./routes/PrivateRoutes"

const router = createBrowserRouter([
    {
        path: "/",
        element: <Root />,
        errorElement: <ErrorPage />,
        children: [
            {
                path: "/",
                element: <Login />
            },
            {
                path: "/home",
                element: (
                    <PrivateRoute>
                        <Home />
                    </PrivateRoute>
                )
            },
            {
                path: "/register",
                element: <Register />
            },
            {
                path: "/employees",
                element: (
                    <PrivateRoute>
                        <EmployeesList />
                    </PrivateRoute>
                )
            },
            {
                path: "/employees/:id",
                element: (
                    <PrivateRoute>
                        <EmployeeDetails />
                    </PrivateRoute>
                ),
                errorElement: <ErrorPage />,
            },
            {
                path: "/employees/:id/update",
                element: (
                    <PrivateRoute>
                        <EmployeeEdit />
                    </PrivateRoute>
                ),
                errorElement: <ErrorPage />,
            }
        ]
    }
]);

ReactDOM.createRoot(document.getElementById("root")).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
);