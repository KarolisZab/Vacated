import apiService from "./api-service";
import { EmployeeType } from '../services/types';

const URL = '/admin/users';

class EmployeeService {
    
    async getAllEmployees(): Promise<EmployeeType[]> {
        return await apiService.get<EmployeeType[]>(URL);
    }

    async getEmployeeById(employeeId: string): Promise<EmployeeType> {
        return await apiService.get<EmployeeType>(`${URL}/${employeeId}`);
    }

    async updateEmployee(employeeId: string, employeeData: Partial<EmployeeType>): Promise<EmployeeType> {
        return await apiService.patch<EmployeeType>(`${URL}/${employeeId}`, employeeData);
    }

    async deleteEmployee(employeeId: string): Promise<void> {
        return await apiService.delete(`${URL}/${employeeId}`);
    }
}

export default new EmployeeService();