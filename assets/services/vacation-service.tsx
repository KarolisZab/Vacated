import apiService from "./api-service";
import { CalendarDays, VacationType } from '../services/types';

const URL = '/vacations/';

class VacationService {
    
    async getConfirmedAndSelfRequestedVacations(startDate: string, endDate: string): Promise<CalendarDays> {
        const params = { startDate, endDate };
        return await apiService.get<CalendarDays>(URL, params);
    }

    async getVacationByID(vacationId: string): Promise<CalendarDays> {
        return await apiService.get<CalendarDays>(`${URL}/${vacationId}`);
    }

    async getAllVacations(): Promise<VacationType[]> {
        return await apiService.get<VacationType[]>('/admin/all-vacations');
    }

    async getAllCurrentUserVacations(): Promise<VacationType[]> {
        return await apiService.get<VacationType[]>('/user-vacations');
    }

    async updateRequestedVacation(vacationId: string, vacationData: Partial<VacationType>): Promise<VacationType> {
        return await apiService.patch<VacationType>(`/update-vacation/${vacationId}`, vacationData);
    }

    async requestVacation(vacationData: Partial<VacationType>): Promise<VacationType> {
        return await apiService.post<VacationType>(`/request-vacation`, vacationData);
    }

    // Admin
    async rejectVacation(vacationId: string, vacationData: Partial<VacationType>): Promise<VacationType> {
        return await apiService.patch<VacationType>(`/admin/reject-vacation/${vacationId}`, vacationData);
    }

    async confirmVacation(vacationId: string, vacationData: Partial<VacationType>): Promise<VacationType> {
        return await apiService.patch<VacationType>(`/admin/confirm-vacation/${vacationId}`, vacationData);
    }
}

export default new VacationService();