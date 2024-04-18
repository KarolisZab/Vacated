import apiService from "./api-service";
import { TagType } from '../services/types';

const URL = '/admin/tags';

class VacationService {
    
    async getAllTags(): Promise<TagType[]> {
        return await apiService.get<TagType[]>(URL);
    }

    async getOneTag(tagId: string): Promise<TagType> {
        return await apiService.get<TagType>(`${URL}/${tagId}`);
    }

    async updateTag(tagId: string, tagData: Partial<TagType>): Promise<TagType> {
        return await apiService.patch<TagType>(`${URL}/${tagId}`, tagData);
    }

    async deleteTag(tagId: string): Promise<void> {
        return await apiService.delete(`${URL}/${tagId}`);
    }

    async createTag(tagData: Partial<TagType>): Promise<TagType> {
        return await apiService.post<TagType>(`${URL}`, tagData);
    }
}

export default new VacationService();