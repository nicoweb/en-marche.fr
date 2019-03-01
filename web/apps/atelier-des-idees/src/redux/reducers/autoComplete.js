import { AUTOCOMPLETE_TITLE_IDEA } from '../constants/actionTypes';

const initialState = { autoComplete: [] };

function autoComplete(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case AUTOCOMPLETE_TITLE_IDEA:
        return { ...state, autoComplete: payload.data };
    default:
        return state;
    }
}
export default autoComplete;

export const getAutoComplete = state => state.autoComplete;
