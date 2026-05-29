
## Сохраните файл:

1. В Блокноте нажмите **Файл → Сохранить**
2. Убедитесь, что в поле **Тип файла** выбран **"Все файлы (*.*)"**
3. Нажмите **Сохранить**

## Затем в PowerShell выполните:

```powershell
cd C:\WebServer\htdocs\crm-asti

# Проверьте, что файл создался
dir README.md

# Добавить в Git
git add README.md

# Создать коммит
git commit -m "Add README.md"

# Отправить на GitHub
git push

# Проверить статус
git status
