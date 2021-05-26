<template>
    <v-card>
        <v-card-title class="headline">Redeem reward with customer number</v-card-title>
        <v-form
            data-vv-scope="customerNumber"
            :model="customerNumber"
            @submit.prevent="creditCustomer"
            autocomplete="off"
            method="post"
            accept-charset="UTF-8"
        >
            <v-card-text>
                <p class="body-1" v-if="customerNumber.redeemed === false">Select a reward and enter a customer number to redeem a reward for the customer.</p>
                <p class="body-1" v-if="customerNumber.redeemed !== false">Reward successfully redeemed.</p>

                <div class="mb-3">
                    <a href="javascript:void(0);" v-if="customerNumber.redeemed !== false" @click="customerNumber.redeemed = false">Redeem another reward</a>
                </div>

                <v-autocomplete
                    :disabled="customerNumber.redeemed !== false"
                    v-model="customerNumber.reward"
                    :items="rewards"
                    item-value="0"
                    item-text="1"
                    label="Reward"
                    data-vv-name="reward"
                    hide-no-data
                    hide-selected
                    prepend-inner-icon="fas fa-gift"
                    v-validate="'required'"
                    :error-messages="errors.collect('customerNumber.reward')"
                ></v-autocomplete>

                <v-text-field
                    :disabled="customerNumber.redeemed !== false"
                    type="text"
                    v-model="formdata.number"
                    outline
                    label="Customer number"
                    data-vv-name="number"
                    v-validate="'required|min:11|max:11'"
                    :placeholder="formdata.number"
                    v-mask="'###-###-###'"
                    prepend-inner-icon="person"
                    :error-messages="errors.collect('customerNumber.number')"
                ></v-text-field>

                <v-autocomplete
                    :disabled="customerNumber.redeemed !== false"
                    v-if="Object.keys(segments).length > 0"
                    v-model="customerNumber.segments"
                    :items="segments"
                    item-value="0"
                    item-text="1"
                    label="Segments (optional)"
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
                <v-btn color="primary" :loading="customerNumber.loading" :disabled="customerNumber.redeemed !== false" type="submit">Redeem reward</v-btn>
                <v-btn color="secondary" text @click="$emit('data-list-events', {'closeDialog': true, 'reload': false})">Close</v-btn>
            </v-card-actions>

        </v-form>

    </v-card>
</template>

<script>
    import {copyElById, unmask} from '../../utils/helpers';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

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
                default: '/app/data-form',
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
                .get('/app/segments', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
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
                    console.log(this.form);
                    this.tabCount = Object.keys(this.form.items).length
                })
        },
        methods: {
            copyElById,
            unmask,
            getActiveMerchantCode () {
                // Get currently active merchant code
                axios
                    .get('/app/rewards/merchant/active-code', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
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
                            .post('/app/rewards/customer/credit', {
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
                                this.customerNumber.loading = false
                                this.$emit('data-list-events', {'closeDialog': true, 'reload': true})
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
