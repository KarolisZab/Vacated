import * as React from "react";
import * as ReactDOM from "react-dom/client";
import {
    BrowserRouter,
    createBrowserRouter,
    RouterProvider,
} from "react-router-dom";
import './styles/app.css';
import Navbar from "./components/Navbar";
import 'semantic-ui-css/semantic.min.css'
import Home from './routes/home';
import ErrorPage from "./error-page";
import EmployeesList from "./components/EmployeesList";
import Login from "./components/Login";
import Register from "./components/Register";
import EmployeeDetails from "./components/EmployeeDetails"
import EmployeeEdit from "./components/EmployeeEdit"

const router = createBrowserRouter([
    {
        path: "/home",
        element: <Home />,
        errorElement: <ErrorPage />
    },
    {
        path: "/login",
        element: <Login />
    },
    {
        path: "/register",
        element: <Register />
    },
    {
        path: "/employees",
        element: <EmployeesList />,
        errorElement: <ErrorPage />,
    },
    {
        path: "/employees/:id",
        element: <EmployeeDetails />
    },
    {
        path: "/employees/:id/update",
        element: <EmployeeEdit />
    }
]);

ReactDOM.createRoot(document.getElementById("root")).render(
    <React.StrictMode>
        <Navbar />
        <RouterProvider router={router}>
        </RouterProvider>
    </React.StrictMode>
);