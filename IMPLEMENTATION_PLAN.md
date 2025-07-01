
## 2. Rachma Module Improvements

### Database Changes
- update aleardy exist migration to split the `size` field into `width` and `height` fields in the `rachmat` table.
- Add multilingual support by adding language-specific fields to the relevant tables:
  - `categories`: `name_ar`, `name_fr`
  - `rachmat`: `description_ar`, `description_fr` , `title_ar`, `title_fr`
  - `parts`: `name_ar`, `name_fr`
  - `parts_suggestions`: `name_ar`, `name_fr`

### Model Updates
- Update the `Rachma` model to reflect the changes in the database schema.
- Update other relevant models to handle multilingual fields.



## 3. API Enhancements

### Controller Modifications
- Modify existing API controllers to handle categories and Rachma entries based on the selected language.


### Testing
- Test the API after making the changes to ensure it supports language-specific responses.

## 4. Database Changes

### Seeders
- Review and update database seeders to include multilingual data.

### Migrations
- Ensure that all migrations are properly ordered and dependencies are handled.

## 5. Testing



### New Tests
- Add new tests for:

  - Multilingual support
