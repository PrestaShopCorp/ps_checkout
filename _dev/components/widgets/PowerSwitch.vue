<template>
    <div class="mod-switch">
        <label @click="toggleSwitch(false)" :class="{ active : !isActive }">{{ leftText }}</label>
        <label @click="toggleSwitch(true)" :class="{ active : isActive }">{{ rightText }}</label>
        <span class="switch-slide" :class="{ active : isActive }"></span>
    </div>
</template>

<script>
export default {
    name: 'PowerSwitch',
    data () {
        return {
            isActive: this.value
        }
    },
    props: {
        value: Boolean,
        leftText: {
            type: String,
            require: true
        },
        rightText: {
            type: String,
            require: true
        }
    },
    methods: {
        toggleSwitch (value) {
            this.isActive = value
        }
    },
    watch: {
        isActive (val) {
            this.$emit('input', val)
        }
    }
}
</script>

<style scoped lang="scss">
.mod-switch {
    position: relative;
    display: inline-block;
    min-width: 130px;
    min-width: 8.125rem;
    margin-bottom: 1px;
    font-size: 13.008px;
    font-size: .813rem;
    background-color: #eff1f2;
    border-radius: 1px;
    text-align: center;
    vertical-align: middle;
}
.mod-switch label {
    position: relative;
    z-index: 2;
    float: left;
    width: 50%;
    height: 100%;
    padding: .231em .769em;
    margin: 0;
    line-height: 19px;
    vertical-align: middle;
    text-align: center;
    text-transform: uppercase;
    color: #bbcdd2;
    font-weight: 400;
    cursor: pointer;
    transition: color .2s ease-out;
}
.mod-switch .active {
    color: #fff;
}
.switch-slide {
    position: absolute;
    top: 0;
    left: 0;
    padding: 0;
    z-index: 1;
    width: 50%;
    height: 100%;
    color: #fff;
    background-color: #c05c67;
    transition: all .3s ease-out;
}
.switch-slide.active {
    background-color: #2eacce;
    left: 50%;
}
</style>
