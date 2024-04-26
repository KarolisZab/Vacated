import apiService from "./api-service";
import { CalendarDays, VacationType, MonthlyVacationStatistics, PieChartType } from '../services/types';

const URL = '/vacations/';

class VacationService {
    
    async getConfirmedAndSelfRequestedVacations(startDate: string, endDate: string): Promise<CalendarDays> {
        const params = { startDate, endDate };
        return await apiService.get<CalendarDays>(URL, params);
    }

    async getVacationByID(vacationId: string): Promise<CalendarDays> {
        return await apiService.get<CalendarDays>(`${URL}/${vacationId}`);
    }

    async getAllCurrentUserVacations(vacationType: string): Promise<VacationType[]> {
        const params = { vacationType }
        return await apiService.get<VacationType[]>('/user-vacations', params);
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

    async getAllVacations(vacationType: string): Promise<VacationType[]> {
        const params = { vacationType };
        return await apiService.get<VacationType[]>('/admin/all-vacations', params);
    }

    async getConfirmedVacationsDaysCountInThisYear(): Promise<number> {
        return await apiService.get<number>('/admin/all-confirmed-days');
    }

    async getPendingVacationsDaysCountInThisYear(): Promise<number> {
        return await apiService.get<number>('/admin/pending-vacations');
    }

    async getMonthlyVacationStatistics(): Promise<MonthlyVacationStatistics> {
        return await apiService.get<MonthlyVacationStatistics>('/admin/monthly-vacation-statistics');
    }

    async getVacationProgress(): Promise<PieChartType> {
        return await apiService.get<PieChartType>(`/admin/vacation-percentage`);
    }
}

export default new VacationService();