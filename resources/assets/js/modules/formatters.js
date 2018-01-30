import flow from 'lodash/flow';
import values from 'lodash/values';
import flatten from 'lodash/flatten';

export function validationMessages(data) {
    if (typeof data === 'object') {
        return flow(
            values,
            flatten,
        )(data);
    }

    return 'Something went wrong when submitting your request';
}
