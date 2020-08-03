import { storiesOf } from "@storybook/vue";
import i18n from "./lib/i18n";
import Menu from "./components/menu/menu.vue";
export default { title: "Menu" };

const story = storiesOf("Menu", module);

story.add("Default", () => ({
  components: { Menu },
  template: `<menu />`,
  i18n
}));

export const withEmoji = () => "<div>😀 😎 👍 💯</div>";
