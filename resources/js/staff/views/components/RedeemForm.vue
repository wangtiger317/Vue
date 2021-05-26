<template>
    <v-card>
        <v-card-title class="headline">Atribuir prémio</v-card-title>
        <v-form
            data-vv-scope="customerNumber"
            :model="customerNumber"
            @submit.prevent="creditCustomer"
            autocomplete="off"
            method="post"
            accept-charset="UTF-8"
        >
            <v-card-text>
                <p class="body-1" v-if="customerNumber.redeemed === false">Selecione o prémio a atribuir.</p>
                <p class="body-1" v-if="customerNumber.redeemed !== false">Prémio atribuído com sucesso.</p>

                <v-list three-line>
                    <v-list-item >
                        <v-row>
                            <v-col xs12 class="pa-0 col-xl-6 col-sm-6 col-md-6" style="height: 74px;">
                                <div class="pt-0 pb-0 d-flex align-items-center" style="padding-left: 4px;">

                                    <v-list-item-avatar class="mt-2 d-inline-block">
                                        <v-avatar
                                            class=" list-inline-item"
                                            :size="56"
                                            color="grey"
                                        >
                                            <v-img :src="formdata.avatar"></v-img>
                                        </v-avatar>
                                    </v-list-item-avatar>

                                    <v-list-item-content class="d-inline-block">
                                        <div class="mt-0">
                                            <v-list-item-title v-html="formdata.name"></v-list-item-title>
                                            <v-list-item-subtitle v-html="formdata.number"></v-list-item-subtitle>
                                            <v-list-item-subtitle><v-icon size="17">toll</v-icon> <span v-html="new Intl.NumberFormat(formdata.locale.replace('_', '-')).format(formdata.points)"></span></v-list-item-subtitle>
                                        </div>
                                    </v-list-item-content>
                                </div>
                            </v-col>
                            <v-col align-center justify-center class="pa-0 col-xl-6 col-sm-6 col-md-6" style="height: 84px">
                                <v-list-item v-html="parseDateTime(formdata.expiration, 'DD-MM-YYYY', '#D32F2F', '#4CAF50')" class="pa-0"></v-list-item>
                            </v-col>
                        </v-row>

                    </v-list-item>
                </v-list>

                <div class="mb-3">
                    <a href="javascript:void(0);" v-if="customerNumber.redeemed !== false" @click="customerNumber.redeemed = false">Atribuir mais prémios</a>
                </div>

                <v-autocomplete
                    :disabled="customerNumber.redeemed !== false"
                    v-model="customerNumber.reward"
                    :items="rewards"
                    item-value="0"
                    item-text="1"
                    label="Prémio"
                    data-vv-name="reward"
                    data-vv-as="Prémios"
                    hide-no-data
                    hide-selected
                    prepend-inner-icon="fas fa-gift"
                    v-validate="'required'"
                    :error-messages="errors.collect('customerNumber.reward')"
                ></v-autocomplete>

<!--                <v-text-field-->
<!--                    :disabled="customerNumber.redeemed !== false"-->
<!--                    type="text"-->
<!--                    v-model="formdata.number"-->
<!--                    outline-->
<!--                    label="Número de cliente"-->
<!--                    data-vv-name="number"-->
<!--                    data-vv-as="Número"-->
<!--                    v-validate="'required|min:11|max:11'"-->
<!--                    :placeholder="formdata.number"-->
<!--                    v-mask="'###-###-###'"-->
<!--                    prepend-inner-icon="person"-->
<!--                    :error-messages="errors.collect('customerNumber.number')"-->
<!--                ></v-text-field>-->

                <v-autocomplete
                    :disabled="customerNumber.redeemed !== false"
                    v-if="Object.keys(segments).length > 0"
                    v-model="customerNumber.segments"
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
                <v-btn color="primary" :loading="customerNumber.loading" :disabled="customerNumber.redeemed !== false" type="submit">Trocar Prémio</v-btn>
                <v-btn color="secondary" text @click="$emit('data-list-events', {'closeDialog': true, 'reload': true})">Fechar</v-btn>
            </v-card-actions>

        </v-form>

    </v-card>
</template>

<script>
    import {copyElById, unmask} from '../../utils/helpers';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import moment from "moment";

    export default {
        $_veeValidate: {
            validator: 'new'
        },
        data: () => {
            return {
                segments: [],
                rewards: [],
                customerNumber: {
                    loading: false,
                    credited: false,
                    points: null,
                    redeemed: false,
                    reward: null,
                    segments: [],
                    number: null
                },
                activeMerchantCodeExpires: null,
                merchantCodeCounter: 1,
                merchant: {
                    generateNew: true,
                    loading: false,
                    code: '',
                    expiresSelected: 'day'
                },
                dialog2: {
                    claim: {
                        customerNumber: false
                    }
                },
                form: [],
                formdata: {}
            }
        },
        props: {
            api: {
                default: '/staff/data-form',
                required: false,
                type: String
            },
            model: {
                default: '',
                required: false,
                type: String
            },
            uuid: {
                default: null,
                required: false,
                type: String
            }
        },
        mounted () {
            axios
                .get('/staff/segments', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
                .then(response => {
                    this.segments = _.toPairs(response.data)
                })

            axios
                .get('/staff/rewards', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
                .then(response => {
                    this.rewards = _.toPairs(response.data)
                })
            this.getActiveMerchantCode()
        },
        created () {
            moment.locale(this.$auth.user().locale)
        },
        beforeMount () {
            this.getDataFromApi()
                .then(data => {
                    this.form = data.form
                    this.tabCount = Object.keys(this.form.items).length
                })
        },
        methods: {
            copyElById,
            unmask,
            getActiveMerchantCode () {
                // Get currently active merchant code
                axios
                    .get('/staff/rewards/merchant/active-code', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
                    .then(response => {
                        if (typeof response.data.code !== 'undefined') {
                            this.merchant.code = response.data.code
                            this.merchant.generateNew = false
                            this.activeMerchantCodeExpires = response.data.expires_at
                            this.merchantCodeCounter ++
                        }
                    })

            },
            creditCustomer () {
                this.customerNumber.loading = true
                // validation
                this.$validator.validateAll('customerNumber').then((valid) => {
                    if (! valid) {
                        this.customerNumber.loading = false
                        return false
                    } else {
                        axios
                            .post('/staff/rewards/customer/credit', {
                                locale: this.$i18n.locale,
                                campaign: this.$store.state.app.campaign.uuid,
                                reward: this.customerNumber.reward,
                                number: this.unmask(this.formdata.number, '###-###-###'),
                                segments: this.customerNumber.segments
                            })
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.customerNumber.redeemed = true
                                }
                                this.customerNumber.redeemed = true
                                this.customerNumber.loading = false
                                this.$emit('data-list-events', {'closeDialog': false, 'reload': false})
                            })
                            .catch(err => {

                                let errors = err.response.data.errors || {}

                                for (let field in errors) {
                                    this.$validator.errors.add({
                                        field: 'customerNumber.' + field,
                                        msg: errors[field]
                                    })
                                }
                                this.customerNumber.loading = false
                            })
                    }
                });
            },
            getDataFromApi () {
                this.loading = true
                return new Promise((resolve, reject) => {
                    let that = this
                    axios.get(this.api, {
                        params: {
                            locale: this.$i18n.locale,
                            model: this.model,
                            uuid: this.uuid
                        }
                    })
                        .then(res => {
                            if (res.data.status === 'success') {
                                let form = {}

                                form.items = res.data.form
                                form.loading = false
                                form.error = ''
                                form.errors = {}
                                form.has_error = false
                                form.success = false

                                that.settings = res.data.settings
                                that.formdata = res.data.values

                                // that.relations = res.data.relations
                                // that.count = res.data.count
                                // that.max = res.data.max
                                // that.loading = false
                                // that.loadingForm = false

                                // Dates
                                for (let date of res.data.dates) {
                                    if (that.formdata[date] !== null) {
                                        that.formdata[date] =  new Date(that.formModel[date])
                                    }
                                }

                                resolve({
                                    form
                                })
                            }
                        })
                        .catch(err => console.log(err.response.data))
                        .finally(() => that.customerNumber.loading = false)
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
                    // return '<v-list-item-title class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 56px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 1rem"><div style="line-height: 1.2; text-align: center;flex: 1 1 100%">'+datetime+'</div><div style="line-height: 1.2; font-size: 0.875rem; text-align: center; flex: 1 1 100%">Validade</div></span></div>'

                    // return '<div class="v-avatar v-list-item__avatar mt-0" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div>'

                    return '<div class="v-avatar v-list-item__avatar mt-1" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0 pl-1"><div class="mt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div></div> '
                    // return '<div class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 36px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 16px"><div style="height: 18px; text-align: center">'+datetime+'</div><div style="height: 18px; font-size: 16px; text-align: center">Validade</div></span></div>'

                } else {
                    return ''
                }
            },
        },
        computed: {
            campaign () {
                return this.$store.state.app.campaign
            },
            redeemOptions() {
                return [
                    {
                        active: (_.indexOf(this.campaign.redeemOptions, 'customerNumber') >= 0) ? true : false,
                        id: 'customerNumber',
                        icon: 'card_giftcard',
                        title: 'Customer Number',
                        description: 'Redeem a reward with a customer number.'
                    }
                ]
            }
        }
    }
</script>

<style scoped>

</style>
