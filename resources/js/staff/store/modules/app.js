import Cookies from 'js-cookie'

const app = {
  state: {
    campaign: window.initCampaign,
    language: Cookies.get('language') || 'en',
  },
  mutations: {
    SET_LANGUAGE: (state, language) => {
      state.language = language
      Cookies.set('language', language)
    },
    SET_LOADING: (state, loading) => {
        state.loading = loading
    },
    UPDATE_CAMPAIGN: (state, campaign) => {
      state.campaign = campaign
    }
  },
  actions: {
    setLanguage({ commit }, language) {
      commit('SET_LANGUAGE', language)
    },
      setLoading({ commit }, loading) {
          commit('SET_LOADING', loading)
      },
    updateCampaign({ commit }, campaign) {
      commit('UPDATE_CAMPAIGN', campaign)
    }
  }
}

export default app
