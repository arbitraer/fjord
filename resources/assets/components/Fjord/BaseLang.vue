<template>
    <div class="btn-group btn-group-sm" role="group">
        <button
            class="btn"
            :class="[
                {
                    'btn-primary': lang == lng,
                    'btn-secondary': lang != lng
                }
            ]"
            v-for="lang in languages"
            @click="switchLanguage(lang)"
        >
            {{ lang }}
        </button>
    </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
    name: 'LangSelect',
    computed: {
        ...mapGetters(['lng'])
    },
    props: {
        languages: {
            require: true,
            type: Array
        },
        currentLanguage: {
            require: true,
            type: String
        }
    },
    methods: {
        switchLanguage(language) {
            this.$store.commit('setLanguage', language)
        },
    },

}
</script>

<style lang="css" scoped>
</style>
