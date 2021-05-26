<template>
    <div class="pb-5">
        <v-container grid-list-lg fluid class="pa-0">
            <v-layout row wrap>
                <v-flex class="py-0" xs12 :style="{'background-color': campaign.theme.secondaryColor, 'color': campaign.theme.secondaryTextColor, 'height':'130px'}">
                    <v-img
                        :src="campaign.card.headerImg || ''"
                        :height="campaign.card.headerHeight"
                    >
                        <v-overlay
                            absolute
                            :color="campaign.theme.secondaryColor"
                            :opacity="campaign.card.headerOpacity"
                            z-index="1"
                        >
                        </v-overlay>
                        <v-container fill-height>
                            <v-layout row wrap align-center style="width: 100%;margin: auto">
                                <v-flex
                                    v-if="$auth.check()"
                                    xs12
                                    style="z-index: 2"
                                >
                                    <v-flex
                                        align-center
                                        justify-center
                                        text-xs-center
                                    >
                                        <v-avatar
                                            :tile="false"
                                            :size="48"

                                        >
                                            <img :src=$auth.user().avatar alt="avatar">
                                        </v-avatar>
                                    </v-flex>
                                    <v-flex
                                        align-center
                                        justify-center
                                        text-xs-center
                                        style="padding-top: 0"
                                    >
                                        <div style="font-size: 1rem">{{ $auth.user().name }}</div>
                                    </v-flex>
                                </v-flex>
                            </v-layout>
                        </v-container>
                        <template v-slot:placeholder>
                            <v-layout
                                fill-height
                                align-center
                                justify-center
                                ma-0
                                v-if="campaign.contact.headerImg"
                            >
                                <v-progress-circular indeterminate :style="{'color': campaign.theme.secondaryTextColor}"></v-progress-circular>
                            </v-layout>
                        </template>
                    </v-img>
                </v-flex>
                <v-flex mt-3>
                    <v-container  grid-list-lg >
                        <v-layout row wrap >
                            <v-card
                                v-if="earn_or_redeem === 1"
                                v-model="card.claim.qr"
                                min-width="248"

                                class="mx-auto"
                            >
                                <v-card-title v-html="parseDateTime($auth.user().expiration, 'DD-MM-YYYY', '#D32F2F', '#4CAF50')" style="padding: 8px 16px"></v-card-title>
                                <v-divider v-if="$auth.user().expiration !== null" class="mx-4"></v-divider>
                                <v-card-text v-if="card.claim.qrVisible && connectionError === false && linkPointsCredited === null">

                                    <div style="margin: auto; width: 256px;">
                                        <qr-code
                                            class="qr-100"

                                            :text="card.claim.qrUrl"
                                            color="#000000"
                                            bg-color="transparent"
                                            error-level="Q"
                                        >
                                        </qr-code>
                                    </div>
                                    <p class="body-1" style="padding-top:8px;padding-bottom: 8px; margin-bottom: 0; text-align: center">{{ $t('keep_dialog_open_until_confirmation') }}</p>
                                </v-card-text>
                                <v-card-text v-if="linkPointsCredited !== null">
                                    <p class="body-1" v-html="$t('received_points_close_dialog', {points: '<strong>' + linkPointsCredited + '</strong>'})"></p>
                                </v-card-text>
                                <v-card-text v-if="connectionError !== false">
                                    <p class="body-1">A connection error has occured ({{ connectionError }}). Please close this dialog and try again.</p>
                                </v-card-text>
                                <v-card-text v-if="! card.claim.qrVisible">
                                    <v-layout
                                        fill-height
                                        align-center
                                        justify-center
                                        style="height: 303px"
                                    >
                                        <v-progress-circular :size="64" indeterminate :style="{'color': campaign.theme.textColor}"></v-progress-circular>
                                    </v-layout>
                                </v-card-text>
                                <v-divider class="mx-4"></v-divider>
                                <v-card-text>
                                    <p style="text-align:center; margin-bottom: 0">{{$auth.user().number}}</p>
                                    <p style="text-align:center; margin-bottom: 0">{{ $t('customer_number') }}</p>
                                </v-card-text>
                            </v-card>
                            <v-card
                                v-else
                                v-model="card.redeem.qr"
                                min-width="248"
                                class="mx-auto"
                            >
                                <v-card-title v-html="parseDateTime($auth.user().expiration, 'DD-MM-YYYY', '#D32F2F', '#4CAF50')" style="padding: 8px 16px"></v-card-title>
                                <v-divider v-if="$auth.user().expiration !== null" class="mx-4"></v-divider>
                                <v-card-text v-if="card.redeem.qrVisible && connectionError === false && rewardRedeemed === null">

                                    <div style="margin: auto; width: 256px;">
                                        <qr-code
                                            class="qr-100"

                                            :text="card.redeem.qrUrl"
                                            color="#000000"
                                            bg-color="transparent"
                                            error-level="Q"
                                        >
                                        </qr-code>
                                    </div>
                                    <p class="body-1" style="padding-top:8px;padding-bottom: 8px; margin-bottom: 0; text-align: center">{{ $t('keep_dialog_open_until_confirmation') }}</p>
                                </v-card-text>
                                <v-card-text v-if="rewardRedeemed !== null">
                                    <p class="body-1" v-html="$t('reward_successfully_redeemed', {rewardRedeemed: '<strong>' + rewardRedeemed + '</strong>'})"></p>
                                    <p class="body-1" v-html="$t('find_rewards_history_tab')"></p>
                                </v-card-text>
                                <v-card-text v-if="connectionError !== false">
                                    <p class="body-1">A connection error has occured ({{ connectionError }}). Please close this dialog and try again.</p>
                                </v-card-text>
                                <v-card-text v-if="! card.redeem.qrVisible">
                                    <v-layout
                                        fill-height
                                        align-center
                                        justify-center
                                        style="height: 303px"
                                    >
                                        <v-progress-circular :size="64" indeterminate :style="{'color': campaign.theme.textColor}"></v-progress-circular>
                                    </v-layout>
                                </v-card-text>
                                <v-divider class="mx-4"></v-divider>
                                <v-card-text>
                                    <p style="text-align:center; margin-bottom: 0">{{$auth.user().number}}</p>
                                    <p style="text-align:center; margin-bottom: 0">{{ $t('customer_number') }}</p>
                                </v-card-text>
                            </v-card>
                        </v-layout>
                    </v-container>
                </v-flex>
            </v-layout>
        </v-container>
    </div>
</template>
<script>
    import {copyElById} from '../../utils/helpers';
    import _ from 'lodash'
    import moment from 'moment'

    export default {
        name: 'app',
        $_veeValidate: {
            validator: 'new'
        },
        data () {
            return {
                locale: 'en',
                points: null,
                segments: [],
                rewards: [],
                socket: null,
                connectionError: false,
                selectedReward: null,
                rewardRedeemed: null,
                redeemedReward: null,

                linkPointsCredited: null,
                earn_or_redeem: null,
                selected_gallery_image: null,
                name: this.$auth.user().name,
                card: {
                    redeem: {
                        qr: true,
                        qrVisible: false,
                        qrUrl: '',
                        merchant: false,
                        showMerchantCode: false,
                        customerNumber: false
                    },
                    claim: {
                        qr: false,
                        qrVisible: false,
                        qrUrl: '',
                        code: false,
                        merchant: false,
                        showMerchantCode: false,
                        customerNumber: false
                    }
                },

                merchantCode: {
                    loading: false,
                    verfied: false,
                    processed: false,
                    code: ''
                },
                merchantCodeVerified: {
                    loading: false,
                    reward: '',
                    code: ''
                }
            }
        },
        mounted() {
            this.getEarnOrRedeem();
        },
        computed: {
            campaign () {
                console.log(this.$store.state.app.campaign)
                this.selectedReward = this.$store.state.app.campaign.rewards.list[0].uuid

                return this.$store.state.app.campaign
            },
        },
        methods:{
            copyElById,
            formatNumber (number) {
                return new Intl.NumberFormat(this.locale.replace('_', '-')).format(number)
            },
            getDate(date) {
                return moment(date).format("ll");
            },

            generateRedeemToken () {

                axios
                    .post('/campaign/get-redeem-reward-token', {
                        locale: this.$i18n.locale,
                        campaign: this.$store.state.app.campaign.uuid,
                        reward: this.selectedReward
                    })
                    .then(response => {
                        if (response.data.status === 'success') {
                            let that = this
                            let token = response.data.token
                            let root = this.$store.state.app.campaign.root
                            let scheme = this.$store.state.app.campaign.scheme
                            let url = scheme + '://' + root + '/staff#/rewards/link?token=' + token
                            this.card.redeem.qrUrl = url
                            this.card.redeem.qrVisible = true

                            console.log(url)

                            if (this.socket === null || this.socket.connection.state == 'disconnected') {
                                // Enable pusher logging - don't include this in production
                                //Pusher.logToConsole = true

                                //let channel_id = Math.random().toString(36).substr(2, 9)
                                //let csrfToken = document.head.querySelector('meta[name="csrf-token"]').content

                                this.socket = new Pusher(window.initConfig.pusher.key, {
                                    cluster: window.initConfig.pusher.options.cluster,
                                    forceTLS: window.initConfig.pusher.options.encrypted
                                })

                                let channel = this.socket.subscribe(token)

                                this.socket.connection.bind( 'error', function( err ) {
                                    this.connectionError = err.error.data.code
                                });

                                channel.bind('redeemed', function(data) {
                                    that.rewardRedeemed = data.reward
                                    that.points = data.points
                                })
                            }
                        } else {
                            //
                        }
                    })
                    .catch(error => {
                        // Error
                    })
            },

            generateClaimToken () {
                axios
                    .post('/campaign/get-claim-points-token', {
                        locale: this.$i18n.locale,
                        campaign: this.$store.state.app.campaign.uuid
                    })
                    .then(response => {
                        if (response.data.status === 'success') {
                            let that = this
                            let token = response.data.token
                            let root = this.$store.state.app.campaign.root
                            let scheme = this.$store.state.app.campaign.scheme
                            let url = scheme + '://' + root + '/staff#/points/link?token=' + token
                            this.card.claim.qrUrl = url
                            this.card.claim.qrVisible = true

                            console.log(url)

                            if (this.socket === null || this.socket.connection.state == 'disconnected') {
                                // Enable pusher logging - don't include this in production
                                //Pusher.logToConsole = true

                                //let channel_id = Math.random().toString(36).substr(2, 9)
                                //let csrfToken = document.head.querySelector('meta[name="csrf-token"]').content

                                this.socket = new Pusher(window.initConfig.pusher.key, {
                                    cluster: window.initConfig.pusher.options.cluster,
                                    forceTLS: window.initConfig.pusher.options.encrypted
                                })

                                let channel = this.socket.subscribe(token)

                                this.socket.connection.bind( 'error', function( err ) {
                                    this.connectionError = err.error.data.code
                                });

                                channel.bind('credited', function(data) {
                                    that.linkPointsCredited = data.points
                                })
                            }
                        } else {
                            //
                        }
                    })
                    .catch(error => {
                        // Error
                    })
            },

            getEarnOrRedeem(){
                axios
                    .post('/campaign/get-earn-or-redeem', {
                        locale: this.$i18n.locale,
                        campaign: this.$store.state.app.campaign.uuid
                    })
                    .then(response => {
                        if(response.data.status === 'success'){
                            this.earn_or_redeem = response.data.earn_or_redeem

                            if(this.earn_or_redeem === 1){
                                console.log('earn point')
                                this.card.claim.qrVisible = false

                                this.generateClaimToken()
                            }else{
                                console.log('redeem point')
                                this.card.redeem.qrVisible = false
                                this.generateRedeemToken()
                            }
                        }
                    })
                    .catch(error => {
                        //Error
                    })
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
                    return '<div class="mx-auto d-flex justify-content-center" style="min-width: 150px"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 48px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 16px"><div style="height: 24px; text-align: center">'+datetime+'</div><div style="height: 24px; font-size: 16px; text-align: center">Validade</div></span></div>'
                    // return '<div class="mx-lg-auto" style="color: ' + color + '"><span class="v-btn__content"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 16px;">'+icon+'</i>' + datetime + '</span></div>'
                } else {
                    return ''
                }
            },
        },

    };
</script>
