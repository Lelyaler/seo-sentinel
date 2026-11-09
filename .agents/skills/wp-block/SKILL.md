---
name: wp-block
description: Инструкция по созданию современных Gutenberg-блоков на React с поддержкой FSE и SSR
---

# Создание кастомного Gutenberg-блока

Когда пользователь просит создать блок:
1. Создай директорию блока в `src/blocks/<block-name>/`.
2. Сформируй `block.json` (версия v3, apiVersion: 3).
3. Создай `edit.tsx` (или `.js`) с использованием `@wordpress/block-editor` и `@wordpress/components`.
4. Для серверного рендера создай `render.php` и укажи `"render": "file:./render.php"` в `block.json`.
5. Запусти `npm run build` в терминале и убедись, что сборка прошла без ошибок.
