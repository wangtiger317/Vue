<template>
  <div>
    <v-container grid-list-lg fluid class="pb-0">
      <v-layout row wrap>
        <v-flex>
          <v-container style="max-width: 990px" class="pb-0">
            <v-layout row wrap class="mb-5 mt-3">
              <v-flex sm12 md7 lg7>
                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.4s" class="display-3 text-xs-center text-md-left">
                    <v-img src="/images/site/visuals/mobile-history.png" class="mb-0" style="max-width: 400px; margin: auto">
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
              <v-flex sm12 md5 lg5>
                <div class="mx-5">
                  <transition enter-active-class="bounceInLeft">
                    <div v-show="animateTransition" style="animation-duration: 0.8s" :class="{'display-1' : $vuetify.breakpoint.xs, 'display-3' : $vuetify.breakpoint.sm, 'display-3' : $vuetify.breakpoint.smAndUp, 'mt-5' : $vuetify.breakpoint.mdAndUp}" class="text-xs-center text-md-left mb-4">Fidelize os seus clientes</div>
                  </transition>
                  <transition enter-active-class="fadeIn">
                    <div class="text-xs-center text-md-left" v-show="animateTransition">
                      <div class="body-1">Poderá atribuir pontos aos seus clientes de forma digital. Os seus clientes poderão consultar sempre os pontos atribuídos.</div>
                      <div class="mt-3">
                        <v-btn :color="website.theme.outlined" outlined style="border-width: 2px" href="/go#/register">Experimentar Grátis</v-btn>
                      </div>
                    </div>
                  </transition>
                </div>
              </v-flex>
            </v-layout>
          </v-container>
        </v-flex>
      </v-layout>
    </v-container>

    <v-container grid-list-lg fluid class="pt-0 mb-3">
      <v-layout row wrap>
        <v-flex>
          <v-container style="max-width: 990px" grid-list-lg class="pt-0">
            <v-layout row wrap>
              <v-flex d-flex xs6 sm4 md4 lg3 v-for="(feature, index) in _.toPairs(website.features)" :key="index">
                <transition enter-active-class="zoomIn">
                  <v-card class="mx-auto" outlined v-show="animateTransition" style="animation-duration: 0.2s">
                    <v-card-title class="title" v-html="feature[0]"></v-card-title>
                    <v-card-text class="body-1" v-html="feature[1]"></v-card-text>
                  </v-card>
                </transition>
              </v-flex>
            </v-layout>
          </v-container>
        </v-flex>
      </v-layout>
    </v-container>

    <v-container grid-list-lg fluid :class="website.theme.primary" class="pa-0" style="position: relative;">
      <svg version="1.1" x="0px" y="0px" viewBox="0 0 2979 108" style="enable-background:new 0 0 2979 108;display:inline-block;height:110px;position: absolute; right:0 ;top: -10px" xml:space="preserve">
        <polyline :fill="website.theme.backgroundColor" points="2979,0 2979,108 0,0 "/>
      </svg>
      <v-layout row wrap>
        <v-flex>
          <v-container style="max-width: 990px; margin-top: 140px">
            <v-layout row wrap>
              <v-flex order-xs2 order-md1 sm12 md5 lg5>
                <div class="mx-4 my-5">
                  <transition enter-active-class="bounceInLeft">
                    <div v-show="animateTransition" style="animation-duration: 0.8s">
                      <div class="display-3 pt-3 mt-3 mb-2 text-xs-center text-md-left">Os clientes aderem para ganhar pontos</div>
                    </div>
                  </transition>
                  <transition enter-active-class="fadeIn">
                    <div class="mt-3 text-xs-center text-md-right" v-show="animateTransition">
                      <div class="body-1 text-xs-center text-md-left">Adicione pontos aos seus clientes em cada compra.</div>
                    </div>
                  </transition>
                </div>
              </v-flex>
              <v-flex order-xs1 order-md2 sm12 md7 lg7>
                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.5s" class="display-3 text-xs-center text-md-left">
                    <v-img src="/images/site/visuals/earn-points.png" style="z-index: 1;">
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
            </v-layout>
          </v-container>
        </v-flex>
      </v-layout>
    </v-container>

    <v-container grid-list-lg fluid class="pa-0" style="position: relative;background-color: #efefef">
      <svg version="1.1" x="0px" y="0px" viewBox="0 0 2979 108" style="enable-background:new 0 0 2979 108;display:inline-block;height:110px;position: absolute; left:0 ;top: -10px" xml:space="preserve">
        <polyline :fill="website.theme.color" points="2979,0 0,108 0,0 "/>
      </svg>
      <v-layout row wrap>
        <v-flex>
          <v-container style="max-width: 990px; margin-top: 140px; margin-bottom: 20px">
            <v-layout row wrap>
              <v-flex sm12 md7 lg7>
                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.5s" class="display-3 text-xs-center text-md-left">
                    <v-img src="/images/site/visuals/rewards.png" style="z-index: 1">
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
              <v-flex sm12 md5 lg5>
                <div class="mx-4 my-5">
                  <transition enter-active-class="bounceInRight">
                    <div v-show="animateTransition" style="animation-duration: 0.8s">
                      <div class="display-3 mb-2 pt-3 mt-3 text-xs-center text-md-left">Os pontos podem ser trocados por prémios</div>
                    </div>
                  </transition>
                  <transition enter-active-class="fadeIn">
                    <div class="mt-3 text-xs-center text-md-right" v-show="animateTransition">
                      <div class="body-1 text-xs-center text-md-left">Quando o cliente tiver pontos suficientes, pode atribuir-lhe um prémio.</div>
                    </div>
                  </transition>
                </div>
              </v-flex>
            </v-layout>
          </v-container>
        </v-flex>
      </v-layout>
    </v-container>


    <v-container grid-list-lg fluid :class="website.theme.primary" class="pa-0" style="position: relative;">
      <svg version="1.1" x="0px" y="0px" viewBox="0 0 2979 108" style="enable-background:new 0 0 2979 108;display:inline-block;height:110px;position: absolute; right:0 ;top: -10px" xml:space="preserve">
        <polyline fill="#efefef" points="2979,0 2979,108 0,0 "/>
      </svg>
      <v-layout row wrap>
        <v-flex>
          <v-container style="max-width: 990px;margin-top: 140px">
            <v-layout row wrap>
              <v-flex order-xs2 order-md1 sm12 md5 lg5>
                <div class="mx-4 my-5">
                  <transition enter-active-class="bounceInLeft">
                    <div v-show="animateTransition" style="animation-duration: 0.8s">
                      <div class="display-3 pt-3 mt-3 mb-2 text-xs-center text-md-left">Conheça os seus clientes</div>
                    </div>
                  </transition>
                  <transition enter-active-class="fadeIn">
                    <div class="mt-3 text-xs-center text-md-right" v-show="animateTransition">
                      <div class="body-1 text-xs-center text-md-left">Tenha registo de todos os pontos ganhos e dos prémios mais requisitados.</div>
                    </div>
                  </transition>
                </div>
              </v-flex>
              <v-flex order-xs1 order-md2 sm12 md7 lg7>
                <transition enter-active-class="zoomIn">
                  <div v-show="animateTransition" style="animation-duration: 0.5s" class="display-3 text-xs-center text-md-left">
                    <v-img src="/images/site/visuals/customers.png" style="z-index: 1">
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
        animateTransition: false
      }
    },
    methods: {
    },
    mounted () {
      this.animateTransition = true
    },
    computed: {
      website () {
        return this.$store.state.app.website
      },
      _ () {
        return _;
      }
    }
  }
</script>
<style scoped>
</style>
