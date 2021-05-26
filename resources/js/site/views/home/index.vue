<template>
  <div>
    <v-container grid-list-lg fluid>
      <v-layout row wrap>
        <v-flex>

          <v-container style="max-width: 990px">
            <v-layout row wrap class="mb-5 mt-3">
              <v-flex order-xs2 sm12 order-md1 md5 lg5>

                <transition enter-active-class="bounceInLeft">
                  <div class="mt-5 text-xs-center text-md-left" v-show="animateTransition" style="animation-duration: 0.8s">
                    <div :class="{'display-1' : $vuetify.breakpoint.xs, 'display-3' : $vuetify.breakpoint.sm, 'display-3' : $vuetify.breakpoint.smAndUp}" class="text-xs-center text-md-left">{{ website.home.title }}</div>
                    <div class="headline mt-4">Fidelize os seus clientes permitindo-lhes ganhar pontos e trocá-los por prémios.</div>
                  </div>
                </transition>

                <transition enter-active-class="fadeIn">
                  <div class="mt-5 text-xs-center text-md-left" v-show="animateTransition">
                    <v-btn :color="website.theme.primary" class="mr-2" style="margin-top:10px" href="/go#/register">Experimentar grátis</v-btn>
                    <v-btn :color="website.theme.outlined" outlined style="border-width: 2px; margin-top: 10px;" href="https://cartaocliente.com/more/">Saber Mais</v-btn>
                    <div class="mt-3 caption grey--text text--darken-1">Oferta de 7 dias de experiência, sem compromisso.</div>
                  </div>
                </transition>

              </v-flex>
              <v-flex order-xs1 sm12 order-md2 md7 lg7>

                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.4s" class="display-3 text-xs-center text-md-left">

                    <v-img src="/images/site/visuals/mobile-campaign.png" class="" style="margin: auto;" :style="[$vuetify.breakpoint.mdAndUp ? {'max-width': '300px'} : {'max-width': '220px'} ]">
                      <template v-slot:placeholder>
                        <v-layout
                          fill-height
                          align-center
                          justify-center
                          ma-0
                          >
                          <v-progress-circular indeterminate :color="$store.state.app.account.app_color"></v-progress-circular>
                        </v-layout>
                      </template>
                    </v-img>

                  </div>
                </transition>

              </v-flex>
            </v-layout>
          </v-container>

        </v-flex>
      </v-layout>
    </v-container>

    <v-container grid-list-lg fluid :class="website.theme.primary" class="pa-0" style="position: relative;">

      <svg version="1.1" x="0px" y="0px" viewBox="0 0 2979 108" style="enable-background:new 0 0 2979 108;display:inline-block;height:110px;position: absolute; left:0 ;top: -10px" xml:space="preserve">
        <polyline :fill="website.theme.backgroundColor" points="2979,0 0,108 0,0 "/>
      </svg>

      <v-layout row wrap>
        <v-flex>

          <v-container style="max-width: 990px">
            <v-layout row wrap>
              <v-flex sm12 md7 lg7>

                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.5s">
                    <v-img src="/images/site/visuals/user-homepage.png">
                      <template v-slot:placeholder>
                        <v-layout
                          fill-height
                          align-center
                          justify-center
                          ma-0
                          >
                          <v-progress-circular indeterminate color="white"></v-progress-circular>
                        </v-layout>
                      </template>
                    </v-img>
                  </div>
                </transition>

              </v-flex>
              <v-flex sm12 md5 lg5>

                <div class="mx-6 my-7">
                  <transition enter-active-class="bounceInRight">
                    <div v-show="animateTransition" style="animation-duration: 0.8s">
                      <div class="subtitle-1 font-weight-thin text-uppercase text-xs-center text-md-left pt-12">Fácil e Rápido</div>
                      <div class="display-1 my-2 text-xs-center text-md-left">Uma plataforma digital para fidelizar os seus clientes</div>
                      <div class="body-1 text-xs-center text-md-left">Atribua pontos aos seus clientes e defina o que podem trocar por pontos.</div>
                    </div>
                  </transition>
                  <transition enter-active-class="fadeIn">
                    <div class="mt-3 text-xs-center text-md-right" v-show="animateTransition">
                      <v-btn text dark class="mr-2" href="https://cartaocliente.com/more/">Ver todas as vantagens <v-icon>arrow_forward</v-icon></v-btn>
                    </div>
                  </transition>
                </div>

              </v-flex>
            </v-layout>
          </v-container>

        </v-flex>
      </v-layout>
    </v-container>
  </div>
</template>
<script>
  export default {
    data () {
      return {
        locale: 'en',
        animateTransition: false,
      }
    },
    methods: {
      getDate(date) {
        return moment(date).format("lll")
      },
      getDateFrom(date) {
        return moment(date).from()
      }
    },
    mounted () {
      this.animateTransition = true
    },
    computed: {
      website () {
        return this.$store.state.app.website
      }
    }
  }
</script>
<style scoped>
</style>
