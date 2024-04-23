import apiService from "./api-service";
import { EmployeeType, EmployeesGetResultType, EmployeeRegistrationData } from '../services/types';

const URL = '/admin/users';

class EmployeeService {
    
    async getEmployees(page: number, limit?: number, filter?: string): Promise<EmployeesGetResultType> {
        const params = { page, limit, filter }
        return await apiService.get<EmployeesGetResultType>(URL, params);
    }

    async getEmployeeById(employeeId: string): Promise<EmployeeType> {
        return await apiService.get<EmployeeType>(`${URL}/${employeeId}`);
    }

    async getEmployeesAvailableVacationDays(): Promise<number> {
        return await apiService.get<number>(`/user/available-days`);
    }

    async updateEmployee(employeeId: string, employeeData: Partial<EmployeeType>): Promise<EmployeeType> {
        return await apiService.patch<EmployeeType>(`${URL}/${employeeId}`, employeeData);
    }

    async deleteEmployee(employeeId: string): Promise<void> {
        return await apiService.delete(`${URL}/${employeeId}`);
    }

    async getEmployeesCount(): Promise<number> {
        return await apiService.get<number>(`/admin/employee-count`);
    }

    async createUser(data: EmployeeRegistrationData): Promise<void> {
        return await apiService.post("/admin/create-user", data);
    }
}

export default new EmployeeService();