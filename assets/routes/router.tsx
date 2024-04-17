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
import MyVacations from "../components/MyVacations"
import AdminRoot from "./admin"
import { AdminPrivateRoute } from "./PrivateRoutes"
import AdminHome from "../components/AdminHome"
import ReservedDaysList from "../components/ReservedDaysList"
import AllVacations from "../components/AdminAllVacations/index"
import Google from "../components/GoogleAuth/index"

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
                path: "error-page",
                element: (
                    <PrivateRoute>
                        <ServerErrorPage />
                    </PrivateRoute>
                )
            },
            {
                path: "vacations",
                element: (
                    <PrivateRoute>
                        <MyVacations />
                    </PrivateRoute>
                )
            },
            {
                path: "google-auth",
                element: (
                    <Google />
                )
            }
        ]
    },
    {
        path: "/admin",
        element: (
            <AdminPrivateRoute>
                <AdminRoot />
            </AdminPrivateRoute>
        ),
        errorElement: <ErrorPage />,
        children: [
            {
                path: "/admin",
                element: (
                    <AdminPrivateRoute>
                        <AdminHome />
                    </AdminPrivateRoute>
                )
            },
            {
                path: "employees",
                element: (
                    <AdminPrivateRoute>
                        <EmployeesList />
                    </AdminPrivateRoute>
                )
            },
            {
                path: "employees/:id",
                element: (
                    <AdminPrivateRoute>
                        <EmployeeDetails />
                    </AdminPrivateRoute>
                ),
            },
            {
                path: "employees/:id/update",
                element: (
                    <AdminPrivateRoute>
                        <EmployeeEdit />
                    </AdminPrivateRoute>
                ),
            },
            {
                path: "reserved-days",
                element: (
                    <AdminPrivateRoute>
                        <ReservedDaysList />
                    </AdminPrivateRoute>
                )
            },
            {
                path: "vacations",
                element: (
                    <AdminPrivateRoute>
                        <AllVacations />
                    </AdminPrivateRoute>
                )
            },
            {
                path: "create-user",
                element: <Register />
            },
        ]
    }
]);

export default router;