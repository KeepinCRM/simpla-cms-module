# Інтеграція KeepinCRM з Simpla CMS #
* Миттєве відправлення даних з SimplaCMS в KeepinCRM при створенні замовлення
* Після зворотного зв'язку на сайті в KeepinCRM створюється задача та лід

## Встановлення ##
1. Встановити Simpla CMS (перевірено на версіях 2.2.4 та 2.3.8)
2. Зареєструватись в KeepinCRM та запросити api-ключ в служби підтримки
3. Скопіювати всі файли з api та config собі на сайт
4. У файлі config/keepincrm.php заповнити api-ключ (api_key) та джерело (source_id), до якого будуть прикріплюватись замовлення та клієнти
5. У файлі api/Simpla.php в кінці масиву $classes після 'managers' => 'Managers'
    ```php
      private $classes = array(
                  ...
        'comments'   => 'Comments',
        'feedbacks'  => 'Feedbacks',
        'notify'     => 'Notify',
        'managers'   => 'Managers'
      );
    ```
    додати:
    ```php
      'keepincrm'  => 'KeepinCrm'
    ```
6. Для відправлення замовлень в KeepinCRM. У файлі view/CartView.php після рядків
    ```php
      $this->notify->email_order_user($order->id);
      $this->notify->email_order_admin($order->id);
    ```
    додати:
    ```php
      $this->keepincrm->createAgreement($order->id);
    ```
7. Для відправлення зворотного зв'язку в KeepinCRM. У файлі view/FeedbackView.php після рядка
    ```php
      $this->notify->email_feedback_admin($feedback_id);
    ```
    додати:
    ```php
      $this->keepincrm->createTask($feedback_id)
    ```
