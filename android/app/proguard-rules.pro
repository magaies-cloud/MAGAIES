# Flutter Wrapper
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.**  { *; }
-keep class io.flutter.util.**  { *; }
-keep class io.flutter.view.**  { *; }
-keep class io.flutter.**  { *; }
-keep class io.flutter.plugins.**  { *; }
-dontwarn io.flutter.**

# Preserve annotations, line numbers, and signature for Google Play crash deobfuscation
-keepattributes *Annotation*,Signature,InnerClasses,EnclosingMethod
-keepattributes SourceFile,LineNumberTable
-renamesourcefileattribute SourceFile

# WebRTC
-keep class org.webrtc.** { *; }
-dontwarn org.webrtc.**

# JNI & Native Plugins
-keepclasseswithmembernames class * {
    native <methods>;
}

# Keep serializable classes
-keepclassmembers class * implements java.io.Serializable {
    static final long serialVersionUID;
    private static final java.io.ObjectStreamField[] serialPersistentFields;
    !static !transient <fields>;
    !private <fields>;
    !private <methods>;
    private void writeObject(java.io.ObjectOutputStream);
    private void readObject(java.io.ObjectInputStream);
    java.lang.Object writeReplace();
    java.lang.Object readResolve();
}

# Suppress harmless warnings from dependencies & preserve Tink/Gson reflection classes
-dontwarn okio.**
-dontwarn okhttp3.**
-keep class com.google.crypto.tink.** { *; }
-dontwarn com.google.crypto.tink.**
-dontwarn androidx.**
-keep class com.google.gson.** { *; }
-dontwarn com.google.gson.**

# Notifications (flutter_local_notifications Gson models)
-keep class com.dexterous.flutterlocalnotifications.** { *; }
