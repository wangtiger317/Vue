<template>
  <v-container fluid fill-height>
    <v-layout align-center justify-center row fill-height style="margin-right:0; margin-left: 0">

      <v-flex xs10 sm10 md6 lg5 xl4 >
        <v-card class="elevation-18">

          <v-card-text v-if="validToken === null">
            <v-progress-linear
              indeterminate
              color="primary"
            ></v-progress-linear>
          </v-card-text>

          <div  v-if="validToken === false">
            <v-card-text>
              <p class="title">Código inválido, já usado ou expirado.</p>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" to="{name: 'dashboard'}">Ir para Atividade</v-btn>
            </v-card-actions>
          </div>

          <div v-if="validToken === true && redeemed === false">
            <v-list three-line>
              <v-list-item>
                  <v-row>
                      <v-col class="pb-0" style="height: 74px">
                          <v-list-item style="padding-left: 20px">
                              <v-list-item-avatar class="ml-2">

                                  <v-avatar
                                      class="ma-2"
                                      :size="56"
                                      color="grey"
                                  >
                                      <v-img :src="customer.avatar"></v-img>
                                  </v-avatar>
                              </v-list-item-avatar>

                              <v-list-item-content class="pt-0">
                                  <div class="mt-0">
                                      <v-list-item-title v-html="customer.name"></v-list-item-title>
                                      <v-list-item-subtitle v-html="customer.number"></v-list-item-subtitle>
                                      <v-list-item-subtitle><v-icon size="17">toll</v-icon> <span v-html="new Intl.NumberFormat(this.$auth.user().locale.replace('_', '-')).format(customer.points)"></span></v-list-item-subtitle>
                                  </div>

                              </v-list-item-content>
                          </v-list-item>

                      </v-col>
                      <v-col align-center justify-center class="pb-0" style="height: 84px">
                          <v-list-item v-html="parseDateTime(customer.expiration, 'DD-MM-YYYY', '#D32F2F', '#4CAF50')"></v-list-item>
                      </v-col>
                  </v-row>


              </v-list-item>
            </v-list>

            <v-form
              data-vv-scope="redeemReward"
              :model="redeemReward"
              @submit.prevent="creditCustomer"
              autocomplete="off"
              method="post"
              accept-charset="UTF-8"
            >
              <v-card-text style="padding-top: 0">
                <p class="body-1">Pode atribuir um prémio abaixo</p>

                  <v-autocomplete
                    :disabled="redeemReward.code !== false"
                    v-model="redeemReward.reward"
                    :items="rewards"
                    item-value="0"
                    item-text="1"
                    label="Prémio"
                    hide-no-data
                    hide-selected
                    prepend-inner-icon="fas fa-gift"
                    :error-messages="errors.collect('redeemReward.reward')"
                  ></v-autocomplete>

                  <v-autocomplete
                    :disabled="redeemReward.code !== false"
                    v-if="Object.keys(segments).length > 0"
                    v-model="redeemReward.segments"
                    :items="segments"
                    item-value="0"
                    item-text="1"
                    label="Segmentos (opcional)"
                    hide-no-data
                    hide-selected
                    chips
                    multiple
                    prepend-inner-icon="category"
                    deletable-chips
                  ></v-autocomplete>

               </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" :loading="redeemReward.loading" :disabled="redeemReward.code !== false" type="submit">Atribuir prémio</v-btn>
              </v-card-actions>

            </v-form>
          </div>

          <div v-if="redeemed === true">
            <v-layout row align-start>
              <v-list-item>
                <v-list three-line>
                  <v-list-item>
                    <v-list-item-avatar class="mt-6 ml-2">
                      <v-avatar
                        class="ma-2"
                        :size="56"
                        color="grey"
                      >
                        <v-img :src="customer.avatar"></v-img>
                      </v-avatar>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title v-html="customer.name"></v-list-item-title>
                      <v-list-item-subtitle v-html="customer.number"></v-list-item-subtitle>
                      <v-list-item-subtitle><v-icon size="17">toll</v-icon> <span v-html="new Intl.NumberFormat(this.$auth.user().locale.replace('_', '-')).format(customer.points)"></span></v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </v-list-item>
            </v-layout>

            <v-card-text>
              <p class="body-1">O prémio foi atribuído.</p>

             </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" to="{name: 'dashboard'}">Ir para Atividade</v-btn>
            </v-card-actions>

          </div>

          </v-card>

        </v-flex>

      </v-layout>
    </v-container>
</template>
<script>
  import _ from 'lodash'
  import moment from 'moment'

  export default {
    $_veeValidate: {
      validator: 'new'
    },
    data () {
      return {
        locale: 'en',
        token: null,
        validToken: null,
        customer: null,
        redeemed: false,
        segments: [],
        rewards: [],
        redeemReward: {
          loading: false,
          reward: null,
          segments: [],
          code: false,
        },
      }
    },
    mounted() {
        // console.log(this.customer.points)
    },
      methods: {
      creditCustomer () {
        this.redeemReward.loading = true
        // validation
        this.$validator.validateAll('redeemReward').then((valid) => {
          if (! valid) {
            this.redeemReward.loading = false
            return false
          } else {
            axios
              .post('/staff/rewards/push/redemption', {
                  locale: this.$i18n.locale,
                  campaign: this.$store.state.app.campaign.uuid,
                  token: this.token,
                  reward: this.redeemReward.reward,
                  segments: this.redeemReward.segments
              })
              .then(response => {
                if (response.data.status === 'success') {
                  this.customer.points = response.data.points
                  this.redeemed = true
                }
                this.redeemReward.loading = false
              })
              .catch(err => {

                let errors = err.response.data.errors || {}

                for (let field in errors) {
                  this.$validator.errors.add({
                    field: 'redeemReward.' + field,
                    msg: errors[field]
                  })
                }
                this.redeemReward.loading = false
              })
          }
        });
      },
      parseDateTime (datetime, format, color_is_past, color_is_future) {
            moment.locale(this.$auth.user().locale)

            let color = null
            let icon = null
            if (typeof color_is_past !== 'undefined' && moment(datetime).isBefore(moment())) {
                color = color_is_past
                icon = 'cancel'
            }
            if (typeof color_is_future !== 'undefined' && moment(datetime).isAfter(moment())) {
                color = color_is_future
                icon = 'check_circle'
            }

            if (datetime === null) {
                datetime = ''
            } else {
                datetime = (format == 'ago') ? moment(datetime).fromNow() : moment(datetime).format(format)
            }

            if (color !== null) {
                // return '<v-list-item-title class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 56px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 1rem"><div style="line-height: 1.2; text-align: center;flex: 1 1 100%">'+datetime+'</div><div style="line-height: 1.2; font-size: 0.875rem; text-align: center; flex: 1 1 100%">Validade</div></span></div>'

                // return '<div class="v-avatar v-list-item__avatar mt-0" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div>'

                return '<div class="v-avatar v-list-item__avatar mt-1" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0 pl-1"><div class="mt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div></div> '
                // return '<div class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 36px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 16px"><div style="height: 18px; text-align: center">'+datetime+'</div><div style="height: 18px; font-size: 16px; text-align: center">Validade</div></span></div>'

            } else {
                return ''
            }
        },
    },
    created () {
      this.token = this.$route.query.token

      axios
        .get('/staff/rewards', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
        .then(response => {
          this.rewards = _.toPairs(response.data)

          axios
            .post('/staff/rewards/validate-link-token', { campaign: this.$store.state.app.campaign.uuid, token: this.token })
            .then(response => {
              this.validToken = response.data.tokenIsValid
              this.customer = response.data.customer
              console.log(this.customer)
              this.redeemReward.reward = response.data.reward
            })

        })

      axios
        .get('/staff/segments', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
        .then(response => {
          this.segments = _.toPairs(response.data)
        })

    },
    computed: {
      campaign () {
        return this.$store.state.app.campaign
      }
    }
  };
</script>
<style scoped>
</style>
