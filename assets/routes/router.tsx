import {
    createBrowserRouter,
} from "react-router-dom"
import Root from "./root"
import Home from './home'
import ErrorPage from "../error-page"
import EmployeesList from "../components/EmployeesList"
import Login from "../components/Login"
import Register from "../components/Register"
import EmployeeDetails from "../components/EmployeeDetails"
import EmployeeEdit from "../components/EmployeeEdit"
import PrivateRoute from "./PrivateRoutes"
import ServerErrorPage from "../server-error-page"

const router = createBrowserRouter([
    {
        path: "/",
        element: <Root />,
        errorElement: <ErrorPage />,
        children: [
            {
                path: "/",
                element: (
                    <PrivateRoute>
                        <Home />
                    </PrivateRoute>
                )
            },
            {
                path: "login",
                element: <Login />
            },
            {
                path: "register",
                element: <Register />
            },
            {
                path: "employees",
                element: (
                    <PrivateRoute>
                        <EmployeesList />
                    </PrivateRoute>
                )
            },
            {
                path: "employees/:id",
                element: (
                    <PrivateRoute>
                        <EmployeeDetails />
                    </PrivateRoute>
                ),
            },
            {
                path: "employees/:id/update",
                element: (
                    <PrivateRoute>
                        <EmployeeEdit />
                    </PrivateRoute>
                ),
            },
            {
                path: "500",
                element: (
                    <PrivateRoute>
                        <ServerErrorPage />
                    </PrivateRoute>
                )
            }
        ]
    }
]);

export default router;