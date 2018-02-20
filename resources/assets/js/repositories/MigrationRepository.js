import axios from 'axios';

class MigrationRepository {
    /**
     * generate - get the generated migration from the backend
     * @returns {Promise}
     */
    generate(data) {
        return axios.post('api/generate', data);
    }
}

export default new MigrationRepository();